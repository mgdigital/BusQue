<?php

namespace MGDigital\BusQue\Handler;

use MGDigital\BusQue\ScheduledCommand;

class ScheduledCommandHandler extends AbstractHandler
{

    public function handleScheduledCommand(ScheduledCommand $command)
    {
        list($queueName, $id, $serialized) = $this->process($command);
        $this->implementation->getSchedulerAdapter()
            ->scheduleCommand($queueName, $id, $serialized, $command->getDateTime());
    }
}
