<?php

namespace MGDigital\BusQue;

interface SchedulerAdapterInterface
{

    public function scheduleCommand(string $queueName, string $id, string $serialized, \DateTime $dateTime);

    public function cancelScheduledCommand(string $queueName, string $id);

    public function clearSchedule(array $queueNames = null, \DateTime $start = null, \DateTime $end = null);

    /**
     * @param \DateTime $now
     * @param int $limit The maximum number of scheduled commands to return.
     * @param \DateTime|null $startTime Return due commands since $startTime or the beginning of time.
     * @return ReceivedScheduledCommand[]
     */
    public function receiveDueCommands(\DateTime $now, int $limit = 100, \DateTime $startTime = null): array;
}
