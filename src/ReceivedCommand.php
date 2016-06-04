<?php

namespace MGDigital\BusQue;

final class ReceivedCommand
{

    private $queueName;
    private $id;
    private $serialized;

    public function __construct(string $queueName, string $id, string $serialized)
    {
        $this->queueName = $queueName;
        $this->id = $id;
        $this->serialized = $serialized;
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
}
