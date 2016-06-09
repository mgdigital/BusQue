<?php

namespace MGDigital\BusQue\Predis;

use MGDigital\BusQue\Exception\CommandNotFoundException;
use MGDigital\BusQue\Exception\TimeoutException;
use MGDigital\BusQue\QueueAdapterInterface;
use MGDigital\BusQue\ReceivedCommand;
use MGDigital\BusQue\ReceivedScheduledCommand;
use MGDigital\BusQue\SchedulerAdapterInterface;
use MGDigital\BusQue\SchedulerWorker;
use Predis\Client;
use Predis\ClientContextInterface;
use Predis\Connection\ConnectionException;

class PredisAdapter implements QueueAdapterInterface, SchedulerAdapterInterface
{

    const LUA_PATH = __DIR__ . '/../../lua';

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function queueCommand(string $queueName, string $id, string $serialized)
    {
        $this->executeLuaScript('queue_message', [$queueName, $id, $serialized]);
    }

    public function awaitCommand(string $queueName, int $timeout = null): ReceivedCommand
    {
        $stopwatchStart = time();
        $this->client->ping();
        try {
            $id = $this->client->brpoplpush(":{$queueName}:queue", ":{$queueName}:consuming", $timeout ?? 0);
        } catch (ConnectionException $e) {
            $id = null;
        }
        if (!$id) {
            if ($timeout !== null) {
                $timeout = time() - $stopwatchStart - $timeout;
                if ($timeout <= 0) {
                    throw new TimeoutException;
                }
            }
            return $this->awaitCommand($queueName, $timeout);
        }
        /* @var $id string */
        $serialized = $this->executeLuaScript('receive_message', [$queueName, $id]);
        return new ReceivedCommand($queueName, $id, $serialized);
    }

    public function getCommandStatus(string $queueName, string $id): string
    {
        return $this->client->hget(":{$queueName}:statuses", $id) ?? self::STATUS_NOT_FOUND;
    }

    public function setCommandCompleted(string $queueName, string $id)
    {
        $this->executeLuaScript('acknowledge_message', [$queueName, $id]);
    }

    public function setCommandFailed(string $queueName, string $id)
    {
        $this->executeLuaScript('reject_message', [$queueName, $id]);
    }

    public function putQueue(string $queueName)
    {
        $this->client->sadd(':queues', [ $queueName ]);
    }

    public function getQueueNames(): array
    {
        return $this->client->smembers(':queues');
    }

    public function getQueuedCount(string $queueName): int
    {
        return $this->client->llen(":{$queueName}:queue");
    }

    public function getQueuedIds(string $queueName, int $offset = 0, int $limit = 10): array
    {
        return $this->client->lrange(":{$queueName}:queue", $offset, $limit);
    }

    public function getConsumingCount(string $queueName): int
    {
        return $this->client->llen(":{$queueName}:consuming");
    }

    public function getConsumingIds(string $queueName, int $offset = 0, int $limit = 10): array
    {
        return $this->client->lrange(":{$queueName}:consuming", $offset, $limit);
    }

    public function readCommand(string $queueName, string $id): string
    {
        $serialized = $this->client->hget(":{$queueName}:messages", $id);
        if ($serialized === null) {
            throw new CommandNotFoundException();
        }
        return $serialized;
    }

    public function clearQueue(string $queueName)
    {
        $this->executeLuaScript('empty_queue', [$queueName]);
    }

    public function deleteQueue(string $queueName)
    {
        $this->clearQueue($queueName);
        $this->clearSchedule([$queueName]);
    }

    public function purgeCommand(string $queueName, string $id)
    {
        $this->executeLuaScript('purge_message', [$queueName, $id]);
    }

    public function scheduleCommand(string $queueName, string $id, string $serialized, \DateTime $dateTime)
    {
        $this->executeLuaScript('schedule_message', [$queueName, $id, $serialized, $dateTime->getTimestamp()]);
    }

    public function cancelScheduledCommand(string $queueName, string $id)
    {
        $this->purgeCommand($queueName, $id);
    }

    public function clearSchedule(array $queueNames = null, \DateTime $start = null, \DateTime $end = null)
    {
        if ($queueNames === null) {
            $queueNames = [ null ];
        }
        foreach ($queueNames as $queueName) {
            $this->executeLuaScript('clear_schedule', [
                $queueName,
                $start ? $start->getTimestamp() : '-inf',
                $end ? $end->getTimestamp() : '+inf'
            ]);
        }
    }

    public function receiveDueCommands(
        \DateTime $now,
        int $limit = SchedulerWorker::DEFAULT_THROTTLE,
        \DateTime $startTime = null
    ): array {
        if ($startTime === null) {
            $start = 0;
        } else {
            $start = $startTime->getTimestamp();
        }
        
        $results = $this->executeLuaScript('receive_due_messages', [
            $start,
            $now->getTimestamp(),
            $limit
        ]);

        $commands = [ ];

        foreach ($results as $result) {
            list($queueName, $id, $message, $score) = $result;
            $commands[ ] = new ReceivedScheduledCommand(
                $queueName,
                $id,
                $message,
                new \DateTime('@'.$score)
            );
        }
        return $commands;
    }

    private function executeLuaScript(string $script, array $args)
    {
        $command = new LuaFileCommand(static::LUA_PATH . '/' . $script . '.lua');
        $command->setArguments($args);
        return $this->client->executeCommand($command);
    }
}
