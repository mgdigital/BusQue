<?php

namespace MGDigital\BusQue;

use MGDigital\BusQue\Exception\TimeoutException;

class QueueWorker
{

    private $implementation;

    public function __construct(Implementation $implementation)
    {
        $this->implementation = $implementation;
    }

    public function work(string $queueName, int $n = null, int $time = null)
    {
        $stopwatchStart = time();
        $queue = $this->implementation->getQueueAdapter();
        $serializer = $this->implementation->getCommandSerializer();
        $commandBus = $this->implementation->getCommandBusAdapter();
        $errorHandler = $this->implementation->getErrorHandler();
        while ($n === null || $n > 0) {
            try {
                $received = $queue->awaitCommand($queueName, $time);
            } catch (TimeoutException $e) {
                break;
            }
            $command = null;
            try {
                $command = $serializer->unserialize($received->getSerialized());
                $commandBus->handle($command);
                $queue->setCommandCompleted($received->getQueueName(), $received->getId());
            } catch (\Throwable $exception) {
                $queue->setCommandFailed($queueName, $received->getId());
                if ($command === null) {
                    $errorHandler->handleUnserializationError(
                        $queueName,
                        $received->getId(),
                        $received->getSerialized(),
                        $exception
                    );
                } else {
                    $errorHandler->handleCommandError($command, $exception);
                }
            }
            if ($n !== null) {
                $n--;
            }
            if ($time !== null && (time() - $stopwatchStart >= $time)) {
                break;
            }
        }
    }
}
