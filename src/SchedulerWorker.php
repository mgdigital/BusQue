<?php

namespace MGDigital\BusQue;

class SchedulerWorker
{

    private $implementation;

    public function __construct(Implementation $implementation)
    {
        $this->implementation = $implementation;
    }

    /**
     * @param int|null $limit The maximum number of commands to schedule.
     * @param int|null $time The maximum amount of time in seconds to work.
     * @param int $uSleepTime The number of microseconds to usleep between each query to the scheduler.
     * @param \DateInterval|null $expiry The expiry interval for an overdue unqueued command.
     */
    public function work(int $limit = null, int $time = null, int $uSleepTime = 5000000, \DateInterval $expiry = null)
    {
        $stopwatchStart = time();
        while ($limit === null || $limit > 0) {
            $now = $this->implementation->getClock()->getTime();
            if ($expiry === null) {
                $start = null;
            } else {
                $start = clone $now;
                $start = $start->sub($expiry);
            }
            $receivedCommands = $this->implementation->getSchedulerAdapter()
                ->receiveDueCommands($now, $limit, $start);
            foreach ($receivedCommands as $received) {
                $this->implementation->getQueueAdapter()
                    ->queueCommand(
                        $received->getQueueName(),
                        $received->getId(),
                        $received->getSerialized()
                    );
                if ($limit !== null) {
                    $limit--;
                }
            }
            if (($limit !== null && $limit <= 0) || ($time !== null && (time() - $stopwatchStart >= $time))) {
                break;
            }
            usleep($uSleepTime);
        }
    }
}
