<?php

namespace MGDigital\BusQue\QueueResolver;

class QueueVote
{

    const CONFIDENCE_LOW = -1;
    const CONFIDENCE_NEUTRAL = 0;
    const CONFIDENCE_MEDIUM = 1;
    const CONFIDENCE_HIGH = 2;
    const CONFIDENCE_VERY_HIGH = 3;

    private $queueName;
    private $confidence;

    public function __construct(string $queueName, int $confidence)
    {
        $this->queueName = $queueName;
        $this->confidence = $confidence;
    }

    public function getQueueName(): string
    {
        return $this->queueName;
    }

    public function getConfidence(): int
    {
        return $this->confidence;
    }
}
