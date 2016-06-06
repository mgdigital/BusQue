<?php

namespace MGDigital\BusQue;

interface QueueResolverInterface
{

    public function resolveQueueName($command): string;
}
