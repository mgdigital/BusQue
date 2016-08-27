<?php

namespace spec\MGDigital\BusQue;

use MGDigital\BusQue\BusQue;
use MGDigital\BusQue\QueuedCommand;
use MGDigital\BusQue\ScheduledCommand;

final class BusQueSpec extends AbstractSpec
{

    public function it_is_initializable()
    {
        $this->shouldHaveType(BusQue::class);
    }

    public function it_can_get_the_queue_name_for_a_command()
    {
        $this->queueResolver->resolveQueueName('test_command')->willReturn('test_queue');
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
        $this->commandBusAdapter->handle(new QueuedCommand('test_command', 'test_id'))->shouldBeCalled();
        $this->queueCommand('test_command', 'test_id');
    }

    public function it_can_schedule_a_command()
    {
        $dateTime = new \DateTime();
        $this->commandBusAdapter->handle(new ScheduledCommand('test_command', $dateTime, 'test_id'))->shouldBeCalled();
        $this->scheduleCommand('test_command', $dateTime, 'test_id');
    }

    public function it_can_check_if_an_id_is_queued()
    {
        $this->queueDriver->isIdQueued('test', 'test')->willReturn(true);
        $this->isIDQueued('test', 'test')->shouldReturn(true);
    }

    public function it_can_get_the_length_of_a_queue()
    {
        $this->queueDriver->getQueuedCount('test_queue')->willReturn(10);
        $this->getQueuedCount('test_queue')->shouldReturn(10);
        $this->getQueuedCount('test_queue');
    }

    public function it_can_purge_a_command()
    {
        $this->queueDriver->purgeCommand('test_queue', 'test_command_id')->shouldBeCalled();
        $this->purgeCommand('test_queue', 'test_command_id');
    }

    public function it_can_delete_a_queue()
    {
        $this->queueDriver->deleteQueue('test_queue')->shouldBeCalled();
        $this->deleteQueue('test_queue');
    }

    public function it_can_list_the_queues()
    {
        $this->queueDriver->getQueueNames()->willReturn(['test_queue']);
        $this->queueDriver->getQueueNames()->shouldBeCalled();
        $this->listQueues()->shouldReturn(['test_queue']);
        $this->listQueues();
    }

    public function it_can_list_the_command_ids_in_a_queue()
    {
        $this->queueDriver->getQueuedIds('test_queue', 0, 10)->willReturn(['test_command_id']);
        $this->queueDriver->getQueuedIds('test_queue', 0, 10)->shouldBeCalled();
        $this->listQueuedIds('test_queue', 0, 10)->shouldReturn(['test_command_id']);
        $this->listQueuedIds('test_queue');
    }

    public function it_can_check_if_a_command_is_in_progress()
    {
        $this->queueDriver->isIdConsuming('test', 'test')->willReturn(true);
        $this->isIdInProgress('test', 'test')->shouldReturn(true);
    }

    public function it_can_list_the_command_ids_in_progress()
    {
        $this->queueDriver->getConsumingIds('test_queue')->willReturn(['test_command_id']);
        $this->queueDriver->getConsumingIds('test_queue')->shouldBeCalled();
        $this->listInProgressIds('test_queue')->shouldReturn(['test_command_id']);
        $this->listInProgressIds('test_queue');
    }

    public function it_can_read_a_command_from_the_queue()
    {
        $this->queueDriver->readCommand('test_queue', 'test_command_id')->willReturn('serialized');
        $this->commandSerializer->unserialize('serialized')->willReturn('test_command');
        $this->queueDriver->readCommand('test_queue', 'test_command_id')->shouldBeCalled();
        $this->commandSerializer->unserialize('serialized')->shouldBeCalled();
        $this->getCommand('test_queue', 'test_command_id')->shouldReturn('test_command');
        $this->getCommand('test_queue', 'test_command_id');
    }
}
