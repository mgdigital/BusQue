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
        while ($n === null || $n > 0) {
            $this->iterate($queueName, $time);
            $n === null || $n--;
            if ($time !== null && (time() - $stopwatchStart >= $time)) {
                break;
            }
        }
    }

    private function iterate(string $queueName, int $time = null)
    {
        try {
            $received = $this->implementation->getQueueAdapter()
                ->awaitCommand($queueName, $time);
        } catch (TimeoutException $e) {
            return;
        }
        $command = null;
        try {
            $command = $this->implementation->getCommandSerializer()
                ->unserialize($received->getSerialized());
            $this->implementation->getCommandBusAdapter()
                ->handle($command);
            $this->implementation->getQueueAdapter()
                ->setCommandCompleted($received->getQueueName(), $received->getId());
        } catch (\Throwable $exception) {
            $this->implementation->getQueueAdapter()
                ->setCommandFailed($queueName, $received->getId());
            if ($command === null) {
                $this->implementation->getErrorHandler()
                    ->handleUnserializationError(
                        $queueName,
                        $received->getId(),
                        $received->getSerialized(),
                        $exception
                    );
            } else {
                $this->implementation->getErrorHandler()
                    ->handleCommandError($command, $exception);
            }
        }
    }
}
