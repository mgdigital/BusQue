<?php

namespace MGDigital\BusQue;

use MGDigital\BusQue\Exception\TimeoutException;

interface SchedulerAdapterInterface
{

    public function scheduleCommand(string $queueName, string $id, string $serialized, \DateTime $dateTime);

    public function cancelScheduledCommand(string $queueName, string $id);

    public function clearSchedule(array $queueNames = null, \DateTime $start = null, \DateTime $end = null);

    /**
     * @param ClockInterface $clock
     * @param int $n The maximum number of scheduled commands to return.
     * @param int $timeout If no scheduled command is encountered before timeout then an empty array should be returned.
     * @return ReceivedScheduledCommand[]
     */
    public function awaitScheduledCommands(ClockInterface $clock, int $n = null, int $timeout = null): array;
}
