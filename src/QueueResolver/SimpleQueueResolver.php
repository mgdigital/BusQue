<?php

namespace MGDigital\BusQue\QueueResolver;

use MGDigital\BusQue\QueueResolverInterface;

class SimpleQueueResolver implements QueueResolverInterface
{

    private $queueName;

    public function __construct(string $queueName)
    {
        $this->queueName = $queueName;
    }

    public function resolveQueueName($command): string
    {
        return $this->queueName;
    }
}
