<?php

namespace spec\MGDigital\BusQue;

use MGDigital\BusQue\BusQue;
use MGDigital\BusQue\QueuedCommand;
use MGDigital\BusQue\ReceivedCommand;
use MGDigital\BusQue\ReceivedScheduledCommand;
use MGDigital\BusQue\ScheduledCommand;

class BusQueSpec extends AbstractSpec
{

    public function it_is_initializable()
    {
        $this->shouldHaveType(BusQue::class);
    }

    public function it_can_get_the_queue_name_for_a_command()
    {
        $this->queueNameResolver->resolveQueueName('test_command')->willReturn('test_queue');
        $this->getQueueName('test_command')->shouldReturn('test_queue');
        $this->getQueueName('test_command');
    }

    public function it_can_serialize_a_command()
    {
        $this->commandSerializer->serialize('test_command')->willReturn('serialized');
        $this->serializeCommand('test_command')->shouldReturn('serialized');
        $this->serializeCommand('test_command');
    }

    public function it_can_unserialize_a_command()
    {
        $this->commandSerializer->unserialize('serialized')->willReturn('test_command');
        $this->unserializeCommand('serialized')->shouldReturn('test_command');
        $this->unserializeCommand('serialized');
    }

    public function it_can_generate_a_command_id()
    {
        $this->commandIdGenerator->generateId('test_command')->willReturn('test_id');
        $this->generateCommandId('test_command')->shouldReturn('test_id');
        $this->generateCommandId('test_command');
    }

    public function it_can_queue_a_command()
    {
        $this->commandBusAdapter->handle(new QueuedCommand('test_command'))->shouldBeCalled();
        $this->queueCommand('test_command');
    }

    public function it_can_schedule_a_command()
    {
        $dateTime = new \DateTime();
        $this->commandBusAdapter->handle(new ScheduledCommand('test_command', $dateTime))->shouldBeCalled();
        $this->scheduleCommand('test_command', $dateTime);
    }

    public function it_can_get_the_status_of_a_command()
    {
        $this->queueAdapter->getCommandStatus('test_queue', 'test_command_id')->willReturn('test_status');
        $this->getCommandStatus('test_queue', 'test_command_id')->shouldReturn('test_status');
        $this->getCommandStatus('test_queue', 'test_command_id');
    }

    public function it_can_get_the_length_of_a_queue()
    {
        $this->queueAdapter->getQueuedCount('test_queue')->willReturn(10);
        $this->getQueuedCount('test_queue')->shouldReturn(10);
        $this->getQueuedCount('test_queue');
    }

    public function it_can_purge_a_command()
    {
        $this->queueAdapter->purgeCommand('test_queue', 'test_command_id')->shouldBeCalled();
        $this->purgeCommand('test_queue', 'test_command_id');
    }

    public function it_can_empty_a_queue()
    {
        $this->queueAdapter->emptyQueue('test_queue')->shouldBeCalled();
        $this->emptyQueue('test_queue');
    }

}