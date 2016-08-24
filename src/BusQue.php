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
        return $this->implementation->getQueueResolver()->resolveQueueName($command);
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

    public function isIdQueued(string $queueName, string $id): bool
    {
        return $this->implementation->getQueueDriver()->isIdQueued($queueName, $id);
    }

    public function getQueuedCount(string $queueName): int
    {
        return $this->implementation->getQueueDriver()->getQueuedCount($queueName);
    }

    public function purgeCommand(string $queueName, string $commandId)
    {
        $this->implementation->getQueueDriver()->purgeCommand($queueName, $commandId);
    }

    public function deleteQueue(string $queueName)
    {
        $this->implementation->getQueueDriver()->deleteQueue($queueName);
    }

    public function listQueues(): array
    {
        return $this->implementation->getQueueDriver()->getQueueNames();
    }

    public function listQueuedIds(string $queueName, int $offset = 0, int $limit = 10): array
    {
        return $this->implementation->getQueueDriver()->getQueuedIds($queueName, $offset, $limit);
    }

    public function isIdInProgress(string $queueName, string $id): bool
    {
        return $this->implementation->getQueueDriver()->isIdConsuming($queueName, $id);
    }

    public function listInProgressIds(string $queueName): array
    {
        return $this->implementation->getQueueDriver()->getConsumingIds($queueName);
    }

    public function getCommand(string $queueName, string $id)
    {
        $serialized = $this->implementation->getQueueDriver()->readCommand($queueName, $id);
        return $this->unserializeCommand($serialized);
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
