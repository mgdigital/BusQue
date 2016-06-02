<?php

namespace spec\MGDigital\BusQue;

use MGDigital\BusQue\CommandHandler;
use MGDigital\BusQue\QueuedCommand;
use MGDigital\BusQue\ScheduledCommand;
use Prophecy\Argument;

final class CommandHandlerSpec extends AbstractSpec
{

    public function it_is_initializable()
    {
        $this->shouldHaveType(CommandHandler::class);
    }

    public function it_can_queue_a_command_with_an_id()
    {
        $queuedCommand = new QueuedCommand('test_command', 'test_id');
        $this->queueAdapter->queueCommand('test_queue', 'test_id', 'serialized')->shouldBeCalled();
        $this->handleQueued($queuedCommand);
    }

    public function it_can_queue_a_command_without_an_id()
    {
        $queuedCommand = new QueuedCommand('test_command');
        $this->queueAdapter->queueCommand('test_queue', 'test_generated_id', 'serialized')->shouldBeCalled();
        $this->handleQueued($queuedCommand);
    }

    public function it_can_schedule_a_command()
    {
        $dateTime = new \DateTime();
        $scheduledCommand = new ScheduledCommand('test_command', $dateTime);
        $this->schedulerAdapter->scheduleCommand('test_queue', 'test_generated_id', 'serialized', $dateTime)->shouldBeCalled();
        $this->handleScheduled($scheduledCommand);
    }
}
