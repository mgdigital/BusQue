<?php

namespace MGDigital\BusQue;

final class ReceivedScheduledCommand
{

    private $queueName;
    private $id;
    private $serialized;
    private $dateTime;

    public function __construct(string $queueName, string $id, string $serialized, \DateTime $dateTime)
    {
        $this->queueName = $queueName;
        $this->id = $id;
        $this->serialized = $serialized;
        $this->dateTime = $dateTime;
    }

    public function getQueueName(): string
    {
        return $this->queueName;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSerialized(): string
    {
        return $this->serialized;
    }

    public function getDateTime(): \DateTime
    {
        return $this->dateTime;
    }
}
