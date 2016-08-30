<?php

namespace MGDigital\BusQue\Redis;

use MGDigital\BusQue\Exception\CommandNotFoundException;
use MGDigital\BusQue\Exception\ConcurrencyException;
use MGDigital\BusQue\Exception\DriverException;
use MGDigital\BusQue\Exception\TimeoutException;
use MGDigital\BusQue\QueueDriverInterface;
use MGDigital\BusQue\ReceivedCommand;
use MGDigital\BusQue\ReceivedScheduledCommand;
use MGDigital\BusQue\SchedulerDriverInterface;
use MGDigital\BusQue\SchedulerWorker;

final class RedisDriver implements QueueDriverInterface, SchedulerDriverInterface
{

    const LUA_PATH = __DIR__ . '/../../lua';

    private $adapter;
    private $namespace;

    public function __construct(RedisAdapterInterface $adapter, string $namespace = '')
    {
        $this->adapter = $adapter;
        if (!preg_match('/^[a-z0-9_\-\.]*$/i', $namespace)) {
            throw new \InvalidArgumentException('Invalid namespace.');
        }
        $this->namespace = $namespace;
    }

    public function queueCommand(string $queueName, string $commandId, string $serialized)
    {
        $this->evalScript('queue_message', [$this->namespace, $queueName, $commandId, $serialized]);
    }

    public function awaitCommand(string $queueName, int $time = null): ReceivedCommand
    {
        $id = $this->adapter->bRPopLPush(
            "{$this->namespace}:{$queueName}:queue",
            "{$this->namespace}:{$queueName}:receiving",
            $time ?? 0
        );
        if (empty($id)) {
            throw new TimeoutException;
        }
        $serialized = $this->evalScript('receive_message', [$this->namespace, $queueName, $id]);
        if (empty($serialized)) {
            throw new ConcurrencyException(sprintf('Cannot currently receive command %s.', $id));
        }
        return new ReceivedCommand($queueName, $id, $serialized);
    }

    public function completeCommand(string $queueName, string $id)
    {
        $this->adapter->sRem("{$this->namespace}:{$queueName}:consuming", [ $id ]);
    }

    public function putQueue(string $queueName)
    {
        $this->adapter->sAdd("{$this->namespace}:queues", [ $queueName ]);
    }

    public function getQueueNames(): array
    {
        return $this->adapter->sMembers("{$this->namespace}:queues");
    }

    public function getQueuedCount(string $queueName): int
    {
        return $this->adapter->lLen("{$this->namespace}:{$queueName}:queue");
    }

    public function isIdQueued(string $queueName, string $id): bool
    {
        return $this->adapter->sIsMember("{$this->namespace}:{$queueName}:queued_ids", $id);
    }

    public function getQueuedIds(string $queueName, int $offset = 0, int $limit = 10): array
    {
        return $this->adapter->lRange("{$this->namespace}:{$queueName}:queue", $offset, $limit);
    }

    public function isIdConsuming(string $queueName, string $id): bool
    {
        return $this->adapter->sIsMember("{$this->namespace}:{$queueName}:consuming", $id);
    }

    public function getConsumingIds(string $queueName): array
    {
        return $this->adapter->sMembers("{$this->namespace}:{$queueName}:consuming");
    }

    public function readCommand(string $queueName, string $id): string
    {
        $serialized = $this->adapter->hGet("{$this->namespace}:{$queueName}:messages", $id);
        if ($serialized === null) {
            throw new CommandNotFoundException();
        }
        return $serialized;
    }

    public function deleteQueue(string $queueName)
    {
        $this->evalScript('empty_queue', [$this->namespace, $queueName]);
    }

    public function purgeCommand(string $queueName, string $id)
    {
        $this->evalScript('purge_message', [$this->namespace, $queueName, $id]);
    }

    public function scheduleCommand(string $queueName, string $id, string $serialized, \DateTime $dateTime)
    {
        $this->evalScript(
            'schedule_message',
            [$this->namespace, $queueName, $id, $serialized, $dateTime->getTimestamp()]
        );
    }

    /**
     * @param string $queueName
     * @param string $id
     * @return \DateTime|null
     */
    public function getScheduledTime(string $queueName, string $id)
    {
        $score = $this->adapter->zScore("{$this->namespace}:schedule", "$queueName||$id");
        if ($score !== null) {
            return new \DateTime("@$score");
        }
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
            $this->evalScript('clear_schedule', [
                $this->namespace,
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
        $results = $this->evalScript('receive_due_messages', [
            $this->namespace,
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

    public function purgeNamespace()
    {
        $this->evalScript('purge_namespace', [$this->namespace]);
    }

    private function evalScript(string $script, array $args)
    {
        $path = self::LUA_PATH . '/' . $script . '.lua';
        $lua = file_get_contents($path);
        return $this->adapter->evalLua($lua, $args);
    }
}
