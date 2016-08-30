<?php

namespace MGDigital\BusQue;

use MGDigital\BusQue\Exception\ConcurrencyException;
use MGDigital\BusQue\Exception\DriverException;
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
            try {
                $this->iterate($queueName, $time);
                $n === null || $n--;
            } catch (DriverException $exception) {
                $this->implementation->getLogger()
                    ->error($exception->getMessage(), compact('exception'));
            } catch (ConcurrencyException $exception) {
                // do nothing
            } catch (TimeoutException $exception) {
                break;
            }
            if ($time !== null && (time() - $stopwatchStart >= $time)) {
                break;
            }
        }
    }

    private function iterate(string $queueName, int $time = null)
    {
        $received = $this->implementation->getQueueDriver()
            ->awaitCommand($queueName, $time);
        $command = $this->implementation->getCommandSerializer()
            ->unserialize($received->getSerialized());
        $this->implementation->getLogger()
            ->debug('Command received', compact('command'));
        try {
            $this->implementation->getCommandBusAdapter()
                ->handle($command, true);
            $this->implementation->getLogger()
                ->info('Command handled', compact('command'));
        } catch (\Throwable $exception) {
            $this->implementation->getLogger()
                ->error('Command failed', compact('command', 'exception'));
        } finally {
            $this->implementation->getQueueDriver()
                ->completeCommand($received->getQueueName(), $received->getId());
        }
    }
}
