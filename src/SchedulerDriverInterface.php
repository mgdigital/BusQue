<?php

namespace MGDigital\BusQue;

interface SchedulerDriverInterface
{

    public function scheduleCommand(string $queueName, string $id, string $serialized, \DateTime $dateTime);

    public function cancelScheduledCommand(string $queueName, string $id);

    public function clearSchedule(array $queueNames = null, \DateTime $start = null, \DateTime $end = null);

    /**
     * @param string $queueName
     * @param string $id
     * @return \DateTime|null
     */
    public function getScheduledTime(string $queueName, string $id);

    /**
     * @param \DateTimeInterface $now
     * @param int $limit The maximum number of scheduled commands to return.
     * @param \DateTimeInterface|null $startTime Return due commands since $startTime or the beginning of time.
     * @return ReceivedScheduledCommand[]
     */
    public function receiveDueCommands(
        \DateTimeInterface $now,
        int $limit = SchedulerWorker::DEFAULT_THROTTLE,
        \DateTimeInterface $startTime = null
    ): array;
}
