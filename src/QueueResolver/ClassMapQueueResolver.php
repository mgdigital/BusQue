<?php

namespace MGDigital\BusQue\QueueResolver;

use MGDigital\BusQue\Exception\QueueResolverException;
use MGDigital\BusQue\QueueResolverInterface;

class ClassMapQueueResolver implements QueueResolverInterface
{

    private $map;

    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function resolveQueueName($command): string
    {
        if (is_object($command)) {
            $class = get_class($command);
            if (isset($this->map[$class])) {
                return $this->map[$class];
            }
        }
        throw new QueueResolverException;
    }
}
