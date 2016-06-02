<?php

namespace MGDigital\BusQue;

interface SchedulerAdapterInterface
{

    public function scheduleCommand(string $queueName, string $id, string $serialized, \DateTime $dateTime);

    public function cancelScheduledCommand(string $queueName, string $id);

    public function clearSchedule(array $queueNames = null, \DateTime $start = null, \DateTime $end = null);

    /**
     * @return ReceivedScheduledCommand[]
     */
    public function awaitScheduledCommands(ClockInterface $clock, int $n = null, int $timeout = null): array;

}