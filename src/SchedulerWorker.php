<?php

namespace MGDigital\BusQue;

class SchedulerWorker
{

    const DEFAULT_THROTTLE = 100;

    private $implementation;

    public function __construct(Implementation $implementation)
    {
        $this->implementation = $implementation;
    }

    /**
     * @param int|null $limit The maximum number of scheduled commands to queue.
     * @param int $throttle The maximum number of scheduled commands to receive at a time.
     * @param int|null $time The maximum amount of time in seconds to work.
     * @param int $uSleepTime The number of microseconds to usleep between each query to the scheduler.
     * @param \DateInterval|null $expiry The expiry interval for an overdue unqueued command.
     */
    public function work(
        int $limit = null,
        int $throttle = self::DEFAULT_THROTTLE,
        int $time = null,
        int $uSleepTime = 5000000,
        \DateInterval $expiry = null
    ) {
        $stopwatchStart = time();
        while ($limit === null || $limit > 0) {
            $queuedCount = $this->iterate($throttle, $expiry);
            if ($limit !== null) {
                $limit -= $queuedCount;
                if ($limit <= 0) {
                    break;
                }
            }
            if ($time !== null && (time() - $stopwatchStart >= $time)) {
                break;
            }
            usleep($uSleepTime);
        }
    }

    private function iterate(int $throttle, \DateInterval $expiry = null): int
    {
        $count = 0;
        $now = $this->implementation->getClock()->getTime();
        if ($expiry === null) {
            $start = null;
        } else {
            $start = clone $now;
            $start = $start->sub($expiry);
        }
        $receivedCommands = $this->implementation->getSchedulerDriver()
            ->receiveDueCommands($now, $throttle, $start);
        foreach ($receivedCommands as $command) {
            $this->handleReceivedCommand($command);
            $count++;
        }
        return $count;
    }

    private function handleReceivedCommand(ReceivedScheduledCommand $command)
    {
        $this->implementation->getQueueDriver()
            ->queueCommand(
                $command->getQueueName(),
                $command->getId(),
                $command->getSerialized()
            );
        $this->implementation->getLogger()->debug('Scheduled command queued', compact('command'));
    }
}
