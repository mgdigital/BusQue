<?php

namespace MGDigital\BusQue;

use MGDigital\BusQue\Exception\CommandNotFoundException;
use MGDigital\BusQue\Exception\TimeoutException;

interface QueueDriverInterface
{

    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_QUEUED = 'queued';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_NOT_FOUND = 'not_found';

    public function queueCommand(string $queueName, string $id, string $serialized);

    public function completeCommand(string $queueName, string $id);

    public function getQueueNames(): array;

    public function getQueuedCount(string $queueName): int;

    public function deleteQueue(string $queueName);

    public function putQueue(string $queueName);

    public function purgeCommand(string $queueName, string $id);

    public function isIdQueued(string $queueName, string $id): bool;

    public function getQueuedIds(string $queueName, int $offset = 0, int $limit = 10): array;

    public function isIdConsuming(string $queueName, string $id): bool;

    public function getConsumingIds(string $queueName): array;

    public function isIdRejected(string $queueName, string $id): bool;

    public function clearRejections(string $queueName);

    /**
     * @param string $queueName
     * @param string $id
     * @return string The serialized command
     * @throws CommandNotFoundException
     */
    public function readCommand(string $queueName, string $id): string;

    /**
     * @param string $queueName
     * @return ReceivedCommand
     * @throws TimeoutException
     */
    public function awaitCommand(string $queueName, int $timeout = null): ReceivedCommand;
}
