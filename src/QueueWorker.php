<?php

namespace MGDigital\BusQue;

use MGDigital\BusQue\Exception\TimeoutException;
use Psr\Log\LogLevel;

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
        $command = $this->implementation->getCommandSerializer()
            ->unserialize($received->getSerialized());
        $this->implementation->getLogger()->debug('Command received', compact('command'));
        try {
            $this->implementation->getCommandBusAdapter()
                ->handle($command, true);
            $this->implementation->getLogger()->info('Command handled', compact('command'));
        } catch (\Throwable $exception) {
            $this->implementation->getLogger()->error('Command failed', compact('command', 'exception'));
        } finally {
            $this->implementation->getQueueDriver()
                ->completeCommand($received->getQueueName(), $received->getId());
        }
    }
}
