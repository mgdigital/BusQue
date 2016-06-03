<?php

namespace MGDigital\BusQue;

interface QueueAdapterInterface
{

    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_QUEUED = 'queued';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_NOT_FOUND = 'not_found';

    public function queueCommand(string $queueName, string $id, string $serialized);

    public function getCommandStatus(string $queueName, string $id): string;

    public function setCommandCompleted(string $queueName, string $id);

    public function setCommandFailed(string $queueName, string $id);

    public function getQueueNames(): array;

    public function getQueuedCount(string $queueName, string $status = null): int;

    public function emptyQueue(string $queueName);

    public function putQueue(string $queueName);

    public function purgeCommand(string $queueName, string $id);

    /**
     * @param string $queueName
     * @return ReceivedCommand
     */
    public function awaitCommand(string $queueName, int $timeout = null): ReceivedCommand;

}