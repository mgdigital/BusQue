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
        while ($n !== 0) {
            $received = $queue->awaitCommand($queueName, $time);
            $command = $serializer->unserialize($received->getSerialized());
            try {
                $commandBus->handle($command);
                $queue->setCommandCompleted($received->getQueueName(), $received->getId());
            } catch (\Exception $exception) {
                $queue->setCommandFailed($received->getQueueName(), $received->getId());
                $errorHandler->handle($command, $exception);
            }
            if ($n !== null) {
                $n--;
            }
            if ($time !== null && (time() - $stopwatchStart >= $time)) {
                throw new TimeoutException;
            }
        }
    }

}