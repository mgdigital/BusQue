<?php

namespace MGDigital\BusQue;

class BusQue
{

    private $implementation;

    public function __construct(Implementation $implementation)
    {
        $this->implementation = $implementation;
    }

    public function getQueueName($command): string
    {
        return $this->implementation->getQueueNameResolver()->resolveQueueName($command);
    }

    public function serializeCommand($command): string
    {
        return $this->implementation->getCommandSerializer()->serialize($command);
    }

    public function unserializeCommand(string $serialized)
    {
        return $this->implementation->getCommandSerializer()->unserialize($serialized);
    }

    public function generateCommandId($command): string
    {
        return $this->implementation->getCommandIdGenerator()->generateId($command);
    }

    public function queueCommand($command, string $commandId = null)
    {
        $this->implementation->getCommandBusAdapter()->handle(new QueuedCommand($command, $commandId));
    }

    public function scheduleCommand($command, \DateTime $dateTime, string $commandId = null)
    {
        $this->implementation->getCommandBusAdapter()->handle(new ScheduledCommand($command, $dateTime, $commandId));
    }

    public function getCommandStatus(string $queueName, string $commandId): string
    {
        return $this->implementation->getQueueAdapter()->getCommandStatus($queueName, $commandId);
    }

    public function getQueuedCount(string $queueName): int
    {
        return $this->implementation->getQueueAdapter()->getQueuedCount($queueName);
    }

    public function purgeCommand(string $queueName, string $commandId)
    {
        $this->implementation->getQueueAdapter()->purgeCommand($queueName, $commandId);
    }

    public function emptyQueue(string $queueName)
    {
        $this->implementation->getQueueAdapter()->emptyQueue($queueName);
    }

    public function listQueues(): array
    {
        return $this->implementation->getQueueAdapter()->getQueueNames();
    }

    public function workQueue(string $queueName, int $n = null, int $time = null)
    {
        (new QueueWorker($this->implementation))->work($queueName, $n, $time);
    }

    public function workSchedule(int $n = null, int $time = null)
    {
        (new SchedulerWorker($this->implementation))->work($n, $time);
    }
}
