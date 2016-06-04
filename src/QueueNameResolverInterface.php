<?php

namespace MGDigital\BusQue;

interface QueueNameResolverInterface
{

    public function resolveQueueName($command): string;
}
