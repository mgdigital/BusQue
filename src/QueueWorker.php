<?php

namespace MGDigital\BusQue;

use MGDigital\BusQue\Exception\SerializerException;
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
            $received = $this->implementation->getQueueDriver()
                ->awaitCommand($queueName, $time);
        } catch (TimeoutException $e) {
            return;
        }
        $command = null;
        try {
            $command = $this->implementation->getCommandSerializer()
                ->unserialize($received->getSerialized());
            try {
                $this->implementation->getCommandBusAdapter()
                    ->handle($command, true);
            } catch (\Throwable $exception) {
                $this->implementation->getErrorHandler()
                    ->handleCommandError($command, $exception);
            }
        } catch (SerializerException $exception) {
            $this->implementation->getErrorHandler()
                ->handleUnserializationError(
                    $queueName,
                    $received->getId(),
                    $received->getSerialized(),
                    $exception
                );
        } finally {
            $this->implementation->getQueueDriver()
                ->completeCommand($received->getQueueName(), $received->getId());
        }
    }
}
