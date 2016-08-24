<?php

namespace MGDigital\BusQue;

use MGDigital\BusQue\Exception\TimeoutException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class QueueWorker
{

    private $implementation;
    private $logger;

    public function __construct(Implementation $implementation, LoggerInterface $logger = null)
    {
        $this->implementation = $implementation;
        $this->logger = $logger;
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
        $command = $this->implementation->getCommandSerializer()
            ->unserialize($received->getSerialized());
        $this->log(LogLevel::DEBUG, 'Command received', compact('command'));
        try {
            $this->implementation->getCommandBusAdapter()
                ->handle($command, true);
            $this->log(LogLevel::INFO, 'Command handled', compact('command'));
        } catch (\Throwable $exception) {
            $this->log(LogLevel::ERROR, 'Command failed', compact('command', 'exception'));
        } finally {
            $this->implementation->getQueueDriver()
                ->completeCommand($received->getQueueName(), $received->getId());
        }
    }

    private function log(string $level, string $message, array $context)
    {
        if ($this->logger !== null) {
            $this->logger->log($level, $message, $context);
        }
    }
}
