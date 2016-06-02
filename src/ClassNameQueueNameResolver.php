<?php

namespace MGDigital\BusQue;

class ClassNameQueueNameResolver implements QueueNameResolverInterface
{

    public function resolveQueueName($command): string
    {
        return get_class($command);
    }

}