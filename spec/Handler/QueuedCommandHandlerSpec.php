<?php

namespace spec\MGDigital\BusQue\Handler;

use MGDigital\BusQue\Handler\QueuedCommandHandler;
use MGDigital\BusQue\QueuedCommand;
use spec\MGDigital\BusQue\AbstractSpec;

final class QueuedCommandHandlerSpec extends AbstractSpec
{

    public function it_is_initializable()
    {
        $this->shouldHaveType(QueuedCommandHandler::class);
    }

    public function it_can_queue_a_command_with_an_id()
    {
        $queuedCommand = new QueuedCommand('test_command', 'test_id');
        $this->queueDriver->queueCommand('test_queue', 'test_id', 'serialized')->shouldBeCalled();
        $this->handleQueuedCommand($queuedCommand);
    }

    public function it_can_queue_a_command_without_an_id()
    {
        $queuedCommand = new QueuedCommand('test_command');
        $this->queueDriver->queueCommand('test_queue', 'test_generated_id', 'serialized')->shouldBeCalled();
        $this->handleQueuedCommand($queuedCommand);
    }
}
