<?php

namespace MGDigital\BusQue;

use MGDigital\BusQue\Exception\TimeoutException;

class SchedulerWorker
{

    private $implementation;

    public function __construct(Implementation $implementation)
    {
        $this->implementation = $implementation;
    }

    public function work(int $n = null, int $time = null)
    {
        $stopwatchStart = time();
        while ($n !== 0) {
            try {
                $received = $this->implementation->getSchedulerAdapter()
                    ->awaitScheduledCommand($this->implementation->getClock(), $time);
            } catch (TimeoutException $e) {
                break;
            }
            $this->implementation->getQueueAdapter()
                ->queueCommand(
                    $received->getQueueName(),
                    $received->getId(),
                    $received->getSerialized()
                );
            if ($n !== null) {
                $n--;
            }
            if ($time !== null && (time() - $stopwatchStart >= $time)) {
                break;
            }
        }
    }
    
}