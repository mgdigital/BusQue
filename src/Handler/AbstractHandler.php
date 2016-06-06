<?php

namespace MGDigital\BusQue\Handler;

use MGDigital\BusQue\BusQueCommandInterface;
use MGDigital\BusQue\Implementation;

abstract class AbstractHandler
{

    protected $implementation;

    public function __construct(Implementation $implementation)
    {
        $this->implementation = $implementation;
    }

    protected function process(BusQueCommandInterface $command): array
    {
        $baseCommand = $command->getCommand();
        $queueName = $this->implementation->getQueueResolver()
            ->resolveQueueName($baseCommand);
        $commandId = $command->getId() ?: $this->implementation->getCommandIdGenerator()
            ->generateId($baseCommand);
        $serialized = $this->implementation->getCommandSerializer()
            ->serialize($baseCommand);
        return [ $queueName, $commandId, $serialized ];
    }
}
