<?php

namespace MGDigital\BusQue\QueueResolver;

use MGDigital\BusQue\Exception\QueueResolverException;
use MGDigital\BusQue\QueueResolverInterface;

class ClassNameQueueResolver implements QueueResolverInterface
{

    public function resolveQueueName($command): string
    {
        if (!is_object($command)) {
            throw new QueueResolverException('The command cannot be converted to a class name queue.');
        }
        return str_replace('\\', '_', get_class($command));
    }
}
