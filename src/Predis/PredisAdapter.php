<?php

namespace MGDigital\BusQue\Predis;

use MGDigital\BusQue\Exception\CommandNotFoundException;
use MGDigital\BusQue\Exception\TimeoutException;
use MGDigital\BusQue\QueueAdapterInterface;
use MGDigital\BusQue\ReceivedCommand;
use MGDigital\BusQue\ReceivedScheduledCommand;
use MGDigital\BusQue\SchedulerAdapterInterface;
use Predis\Client;
use Predis\ClientContextInterface;
use Predis\Connection\ConnectionException;

class PredisAdapter implements QueueAdapterInterface, SchedulerAdapterInterface
{

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function queueCommand(string $queueName, string $id, string $serialized)
    {
        if (!self::cIsCommandIdReserved($this->client, $queueName, $id)) {
            $this->client->pipeline(function (ClientContextInterface $client) use ($queueName, $id, $serialized) {
                self::cStoreCommand($client, $queueName, $id, $serialized);
                self::cReserveCommandId($client, $queueName, $id);
                self::cUpdateCommandStatus($client, $queueName, $id, self::STATUS_QUEUED);
                $client->lpush(":{$queueName}:queue", [$id]);
            });
        }
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
                $timeout = max(0, time() - $stopwatchStart - $timeout);
                if ($timeout <= 0) {
                    throw new TimeoutException;
                }
            }
            return $this->awaitCommand($queueName, $timeout);
        }
        /* @var $id string */
        list(, $serialized) = $this->client->pipeline(function (ClientContextInterface $client) use ($queueName, $id) {
            self::cUpdateCommandStatus($client, $queueName, $id, self::STATUS_IN_PROGRESS);
            self::cRetrieveCommand($client, $queueName, $id);
            self::cReleaseReservedCommandId($client, $queueName, $id);
        });
        return new ReceivedCommand($queueName, $id, $serialized);
    }

    public function getCommandStatus(string $queueName, string $id): string
    {
        return $this->client->hget(":{$queueName}:command_status", $id) ?? self::STATUS_NOT_FOUND;
    }

    public function setCommandCompleted(string $queueName, string $id)
    {
        $this->client->pipeline(function (ClientContextInterface $client) use ($queueName, $id) {
            self::cEndCommand($client, $queueName, $id, self::STATUS_COMPLETED);
        });
    }

    public function setCommandFailed(string $queueName, string $id)
    {
        $this->client->pipeline(function (ClientContextInterface $client) use ($queueName, $id) {
            self::cEndCommand($client, $queueName, $id, self::STATUS_FAILED);
        });
    }

    public function putQueue(string $queueName)
    {
        $this->client->pipeline(function (ClientContextInterface $client) use ($queueName) {
            self::cAddQueue($client, $queueName);
        });
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
        $serialized = self::cRetrieveCommand($this->client, $queueName, $id);
        if ($serialized === null) {
            throw new CommandNotFoundException();
        }
        return $serialized;
    }

    public function emptyQueue(string $queueName)
    {
        self::cEmptyQueue($this->client, $queueName);
    }

    public function deleteQueue(string $queueName)
    {
        $this->client->pipeline(function (ClientContextInterface $client) use ($queueName) {
            self::cDeleteQueue($client, $queueName);
        });
    }

    public function purgeCommand(string $queueName, string $id)
    {
        $this->client->pipeline(function (ClientContextInterface $client) use ($queueName, $id) {
            $client->hdel(":{$queueName}:command_store", [$id]);
            $client->hdel(":{$queueName}:command_status", [$id]);
            self::cReleaseReservedCommandId($client, $queueName, $id);
            $json = json_encode([$queueName, $id]);
            $client->lrem(":{$queueName}:queue", 1, $id);
            $client->lrem(":{$queueName}:consuming", 1, $id);
            $client->zrem(':schedule', $json);
        });
    }

    public function scheduleCommand(string $queueName, string $id, string $serialized, \DateTime $dateTime)
    {
        if (!self::cIsCommandIdReserved($this->client, $queueName, $id)) {
            $this->client->pipeline(
                function (ClientContextInterface $client) use ($queueName, $id, $serialized, $dateTime) {
                    self::cStoreCommand($client, $queueName, $id, $serialized);
                    self::cReserveCommandId($client, $queueName, $id);
                    self::cUpdateCommandStatus($client, $queueName, $id, self::STATUS_SCHEDULED);
                    $json = json_encode([$queueName, $id]);
                    $client->zadd(':schedule', [$json => $dateTime->getTimestamp()]);
                }
            );
        }
    }

    public function cancelScheduledCommand(string $queueName, string $id)
    {
        $this->purgeCommand($queueName, $id);
    }

    public function clearSchedule(array $queueNames = null, \DateTime $start = null, \DateTime $end = null)
    {
        $lowScore = $start ? $start->getTimestamp() : 0;
        $highScore = $end ? $end->getTimestamp() : -1;
        if ($queueNames === null) {
            $queueNames = $this->getQueueNames();
        }
        foreach ($queueNames as $queueName) {
            $result = $this->client->zrangebyscore(':schedule', $lowScore, $highScore);
            if (!empty($result)) {
                $this->client->pipeline(function (ClientContextInterface $client) use ($result, $queueName) {
                    foreach ($result as $json => $score) {
                        list($thisQueueName, $id) = json_decode($json, true);
                        if ($thisQueueName === $queueName) {
                            $client->zrem(':schedule', $json);
                            self::cReleaseReservedCommandId($client, $queueName, $id);
                        }
                    }
                });
            }
        }
    }

    public function receiveDueCommands(\DateTime $now, int $limit = 100, \DateTime $startTime = null): array
    {
        if ($startTime === null) {
            $start = 0;
        } else {
            $start = $startTime->getTimestamp();
        }
        $result = $this->client->zrangebyscore(':schedule', $start, $now->getTimestamp(), [
            'limit' => [0, $limit],
            'withscores' => true,
        ]);
        $commands = [];
        if ($result !== []) {
            $queueNamesById = $idsByJson = [];
            $pipelineReturn = $this->client->pipeline(
                function (ClientContextInterface $client) use (&$result, &$queueNamesById, &$idsByJson) {
                    foreach ($result as $json => $score) {
                        list($queueName, $id) = json_decode($json, true);
                        $queueNamesById[$id] = $queueName;
                        $idsByJson[$json] = $id;
                        self::cRetrieveCommand($client, $queueName, $id);
                    }
                    $client->zrem(':schedule', ...array_keys($result));
                    foreach ($queueNamesById as $id => $queueName) {
                        self::cReleaseReservedCommandId($client, $queueName, $id);
                    }
                }
            );
            foreach (array_keys($result) as $index => $json) {
                $id = $idsByJson[$json];
                $commands[] = new ReceivedScheduledCommand(
                    $queueNamesById[$id],
                    $id,
                    $pipelineReturn[$index],
                    new \DateTime('@' . $result[$json])
                );
            }
        }
        return $commands;
    }

    private static function cStoreCommand($client, string $queueName, string $id, string $serialized)
    {
        self::cAddQueue($client, $queueName);
        $client->hset(":{$queueName}:command_store", $id, $serialized);
    }

    private static function cRetrieveCommand($client, string $queueName, string $id)
    {
        return $client->hget(":{$queueName}:command_store", $id);
    }

    private static function cReserveCommandId($client, string $queueName, string $id)
    {
        $client->sadd(":{$queueName}:queue_ids", [$id]);
    }

    private static function cEndCommand($client, string $queueName, string $id, string $status)
    {
        self::cUpdateCommandStatus($client, $queueName, $id, $status);
        self::cReleaseReservedCommandId($client, $queueName, $id);
        $client->srem(":{$queueName}:queue_ids", [$id]);
        $client->lrem(":{$queueName}:consuming", 1, $id);
    }

    private static function cReleaseReservedCommandId($client, string $queueName, string $id)
    {
        $client->srem(":{$queueName}:queue_ids", [$id]);
    }

    private static function cIsCommandIdReserved($client, string $queueName, string $id)
    {
        return $client->sismember(":{$queueName}:queue_ids", $id);
    }

    private static function cUpdateCommandStatus($client, string $queueName, string $id, string $status)
    {
        $client->hset(":{$queueName}:command_status", $id, $status);
    }

    private static function cAddQueue($client, string $queueName)
    {
        $client->sadd(':queues', [$queueName]);
    }

    private static function cEmptyQueue($client, string $queueName)
    {
        $client->del([
            ":{$queueName}:queue",
            ":{$queueName}:consuming",
            ":{$queueName}:command_store",
            ":{$queueName}:command_status",
            ":{$queueName}:queue_ids"
        ]);
    }

    private static function cDeleteQueue($client, string $queueName)
    {
        self::cEmptyQueue($client, $queueName);
        $client->srem(':queues', $queueName);
    }
}
