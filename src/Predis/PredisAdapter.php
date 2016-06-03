<?php

namespace MGDigital\BusQue\Predis;

use MGDigital\BusQue\ClockInterface;
use MGDigital\BusQue\Exception\TimeoutException;
use MGDigital\BusQue\QueueAdapterInterface;
use MGDigital\BusQue\ReceivedCommand;
use MGDigital\BusQue\ReceivedScheduledCommand;
use MGDigital\BusQue\SchedulerAdapterInterface;
use Predis\Client;
use Predis\Connection\ConnectionException;

class PredisAdapter implements QueueAdapterInterface, SchedulerAdapterInterface
{

    private $client;
    private $schedulerDelay;

    public function __construct(Client $client, int $schedulerDelay = 5)
    {
        $this->client = $client;
        $this->schedulerDelay = $schedulerDelay;
    }

    public function queueCommand(string $queueName, string $id, string $serialized)
    {
        if (!self::_isCommandIdReserved($this->client, $queueName, $id)) {
            $this->client->pipeline(function ($client) use ($queueName, $id, $serialized) {
                self::_storeCommand($client, $queueName, $id, $serialized);
                self::_reserveCommandId($client, $queueName, $id);
                self::_updateCommandStatus($client, $queueName, $id, self::STATUS_QUEUED);
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
        /* @var $queueName string */
        /* @var $id string */
        list(, $serialized) = $this->client->pipeline(function ($client) use ($queueName, $id) {
            self::_updateCommandStatus($client, $queueName, $id, self::STATUS_IN_PROGRESS);
            self::_retrieveCommand($client, $queueName, $id);
        });
        return new ReceivedCommand($queueName, $id, $serialized);
    }

    public function getCommand(string $queueName, string $id): ReceivedCommand
    {
        $serialized = self::_retrieveCommand($this->client, $queueName, $id);
        return new ReceivedCommand($queueName, $id, $serialized);
    }

    public function getCommandStatus(string $queueName, string $id): string
    {
        return $this->client->hget(":{$queueName}:command_status", $id) ?? self::STATUS_NOT_FOUND;
    }

    public function setCommandCompleted(string $queueName, string $id)
    {
        $this->client->pipeline(function ($client) use($queueName, $id) {
            self::_endCommand($client, $queueName, $id, self::STATUS_COMPLETED);
        });
    }

    public function setCommandFailed(string $queueName, string $id)
    {
        $this->client->pipeline(function ($client) use($queueName, $id) {
            self::_endCommand($client, $queueName, $id, self::STATUS_FAILED);
        });
    }

    public function putQueue(string $queueName)
    {
        $this->client->pipeline(function ($client) use($queueName) {
            self::_addQueue($client, $queueName);
        });
    }

    public function getQueueNames(): array
    {
        return $this->client->smembers(':queues');
    }

    public function getQueuedCount(string $queueName, string $status = null): int
    {
        return $this->client->llen(":{$queueName}:queue");
    }

    public function emptyQueue(string $queueName)
    {
        self::_emptyQueue($this->client, $queueName);
    }

    public function deleteQueue(string $queueName)
    {
        $this->client->pipeline(function ($client) use ($queueName) {
            self::_deleteQueue($client, $queueName);
        });
    }

    public function purgeCommand(string $queueName, string $id)
    {
        $this->client->pipeline(function ($client) use ($queueName, $id) {
            $client->hdel(":{$queueName}:command_store", [$id]);
            $client->hdel(":{$queueName}:command_status", [$id]);
            self::_releaseReservedCommandId($client, $queueName, $id);
            $json = json_encode([$queueName, $id]);
            $client->zrem(':schedule', $json);
        });
    }

    public function scheduleCommand(string $queueName, string $id, string $serialized, \DateTime $dateTime)
    {
        if (!self::_isCommandIdReserved($this->client, $queueName, $id)) {
            $this->client->pipeline(function ($client) use ($queueName, $id, $serialized, $dateTime) {
                self::_storeCommand($client, $queueName, $id, $serialized);
                self::_reserveCommandId($client, $queueName, $id);
                self::_updateCommandStatus($client, $queueName, $id, self::STATUS_SCHEDULED);
                $json = json_encode([$queueName, $id]);
                $client->zadd(':schedule', [$json => $dateTime->getTimestamp()]);
            });
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
                $this->client->pipeline(function ($client) use ($result, $queueName) {
                    foreach ($result as $json => $score) {
                        list($thisQueueName, $id) = json_decode($json, true);
                        if ($thisQueueName === $queueName) {
                            $client->zrem(':schedule', $json);
                            self::_releaseReservedCommandId($client, $queueName, $id);
                        }
                    }
                });
            }
        }
    }

    public function awaitScheduledCommands(ClockInterface $clock, int $n = null, int $timeout = null, \DateInterval $expiry = null): array
    {
        $stopwatchStart = time();
        while (true) {
            $currentTime = $clock->getTime();
            if ($expiry === null) {
                $start = 0;
            } else {
                $startTime = clone $currentTime;
                $startTime = $startTime->sub($expiry);
                $start = $startTime->getTimestamp();
            }
            $result = $this->client->zrangebyscore(':schedule', $start, $currentTime->getTimestamp(), [
                'limit' => [0, $n ?? 100],
                'withscores' => true,
            ]);
            $commands = [];
            foreach ($result as $json => $score) {
                list($queueName, $id) = json_decode($json, true);
                $dateTime = new \DateTime("@{$score}");
                list(, $serialized) = $this->client->pipeline(function ($client) use($json, $queueName, $id) {
                    $client->zrem(':schedule', $json);
                    self::_retrieveCommand($client, $queueName, $id);
                    self::_releaseReservedCommandId($client, $queueName, $id);
                });
                $commands[] = new ReceivedScheduledCommand($queueName, $id, $serialized, $dateTime);
            }
            if ($commands !== []) {
                return $commands;
            }
            if ($timeout !== null) {
                if ((time() - $stopwatchStart) >= $timeout) {
                    throw new TimeoutException;
                }
                $sleepTime = min($timeout, $this->schedulerDelay);
            } else {
                $sleepTime = $this->schedulerDelay;
            }
            sleep($sleepTime);
        }
    }

    private static function _storeCommand($client, string $queueName, string $id, string $serialized)
    {
        self::_addQueue($client, $queueName);
        $client->hset(":{$queueName}:command_store", $id, $serialized);
    }

    private static function _retrieveCommand($client, string $queueName, string $id)
    {
        return $client->hget(":{$queueName}:command_store", $id);
    }

    private static function _reserveCommandId($client, string $queueName, string $id)
    {
        $client->sadd(":{$queueName}:queue_ids", [$id]);
    }

    private static function _endCommand($client, string $queueName, string $id, string $status)
    {
        self::_updateCommandStatus($client, $queueName, $id, $status);
        self::_releaseReservedCommandId($client, $queueName, $id);
        $client->srem(":{$queueName}:queue_ids", [$id]);
        $client->lrem(":{$queueName}:consuming", 1, $id);
    }

    private static function _releaseReservedCommandId($client, string $queueName, string $id)
    {
        $client->srem(":{$queueName}:queue_ids", [$id]);
    }

    private static function _isCommandIdReserved($client, string $queueName, string $id)
    {
        return $client->sismember(":{$queueName}:queue_ids", $id);
    }

    private static function _updateCommandStatus($client, string $queueName, string $id, string $status)
    {
        $client->hset(":{$queueName}:command_status", $id, $status);
    }

    private static function _addQueue($client, string $queueName)
    {
        $client->sadd(':queues', [$queueName]);
    }

    private static function _emptyQueue($client, string $queueName)
    {
        $client->del([
            ":{$queueName}:queue",
            ":{$queueName}:consuming",
            ":{$queueName}:command_store",
            ":{$queueName}:command_status",
            ":{$queueName}:queue_ids"
        ]);
    }

    private static function _deleteQueue($client, string $queueName)
    {
        self::_emptyQueue($client, $queueName);
        $client->srem(':queues', $queueName);
    }

}