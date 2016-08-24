<?php

namespace spec\MGDigital\BusQue\Handler;

use MGDigital\BusQue\Handler\ScheduledCommandHandler;
use MGDigital\BusQue\ScheduledCommand;
use spec\MGDigital\BusQue\AbstractSpec;

final class ScheduledCommandHandlerSpec extends AbstractSpec
{

    public function it_is_initializable()
    {
        $this->shouldHaveType(ScheduledCommandHandler::class);
    }

    public function it_can_schedule_a_command()
    {
        $dateTime = new \DateTime();
        $scheduledCommand = new ScheduledCommand('test_command', $dateTime);
        $this->schedulerDriver->scheduleCommand('test_queue', 'test_generated_id', 'serialized', $dateTime)->shouldBeCalled();
        $this->handleScheduledCommand($scheduledCommand);
    }
}
