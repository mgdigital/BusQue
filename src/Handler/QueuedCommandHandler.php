<?php

namespace MGDigital\BusQue\Handler;

use MGDigital\BusQue\QueuedCommand;

class QueuedCommandHandler extends AbstractHandler
{

    public function handleQueuedCommand(QueuedCommand $command)
    {
        list($queueName, $id, $serialized) = $this->process($command);
        $this->implementation->getQueueDriver()
            ->queueCommand($queueName, $id, $serialized);
    }
}
