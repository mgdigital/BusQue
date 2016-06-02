<?php

namespace MGDigital\BusQue;

class ClassNameQueueNameResolver implements QueueNameResolverInterface
{

    public function resolveQueueName($command): string
    {
        return str_replace('\\', '_', get_class($command));
    }

}