<?php

namespace spec\MGDigital\BusQue\Redis;

use MGDigital\BusQue\Redis\RedisAdapterInterface;
use MGDigital\BusQue\Redis\RedisDriver;
use MGDigital\BusQue\SchedulerWorker;
use PhpSpec\ObjectBehavior;

class RedisDriverSpec extends ObjectBehavior
{

    /**
     * @var RedisAdapterInterface
     */
    private $adapter;

    public function let(RedisAdapterInterface $adapter)
    {
        $this->beConstructedWith($adapter, 'test');
        $this->adapter = $adapter;
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(RedisDriver::class);
    }

    public function it_can_queue_a_command()
    {
        $lua = $this->getLua('queue_message');
        $this->adapter->evalLua($lua, ['test', 'test', 'test', 'test'])->shouldBeCalled();
        $this->queueCommand('test', 'test', 'test');
    }

    public function it_can_await_a_queued_command()
    {
        $this->adapter->bRPopLPush('test:test:queue', 'test:test:receiving', 0)->shouldBeCalled()->willReturn('test');
        $lua = $this->getLua('receive_message');
        $this->adapter->evalLua($lua, ['test', 'test', 'test'])->shouldBeCalled()->willReturn('test');
        /* @var \MGDigital\BusQue\ReceivedCommand $receivedCommand */
        $receivedCommand = $this->awaitCommand('test', 0);
        $receivedCommand->getId()->shouldReturn('test');
        $receivedCommand->getQueueName()->shouldReturn('test');
        $receivedCommand->getSerialized()->shouldReturn('test');
    }

    public function it_can_acknowledge_a_completed_command()
    {
        $this->adapter->sRem('test:test:consuming', ['test'])->shouldBeCalled();
        $this->completeCommand('test', 'test');
    }

    public function it_can_add_a_queue()
    {
        $this->adapter->sAdd("test:queues", [ 'test' ])->shouldBeCalled();
        $this->putQueue('test');
    }

    public function it_can_get_all_queue_names()
    {
        $this->adapter->sMembers('test:queues')->shouldBeCalled()->willReturn(['test']);
        $this->getQueueNames()->shouldReturn(['test']);
    }

    public function it_can_count_the_length_of_a_queue()
    {
        $this->adapter->lLen('test:test:queue')->shouldBeCalled()->willReturn(1);
        $this->getQueuedCount('test')->shouldReturn(1);
    }

    public function it_can_check_if_an_id_is_queued()
    {
        $this->adapter->sIsMember('test:test:queued_ids', 'test')->shouldBeCalled()->willReturn(true);
        $this->isIdQueued('test', 'test')->shouldReturn(true);
    }

    public function it_can_get_all_queued_ids()
    {
        $this->adapter->lRange('test:test:queue', 0, 10)->shouldBeCalled()->willReturn(['test']);
        $this->getQueuedIds('test', 0, 10)->shouldReturn(['test']);
    }

    public function it_can_check_if_an_id_is_consuming()
    {
        $this->adapter->sIsMember('test:test:consuming', 'test')->shouldBeCalled()->willReturn(true);
        $this->isIdConsuming('test', 'test')->shouldReturn(true);
    }

    public function it_can_get_currently_consuming_ids()
    {
        $this->adapter->sMembers('test:test:consuming')->shouldBeCalled()->willReturn(['test']);
        $this->getConsumingIds('test')->shouldReturn(['test']);
    }

    public function it_can_read_a_command()
    {
        $this->adapter->hGet('test:test:messages', 'test')->shouldBeCalled()->willReturn('test');
        $this->readCommand('test', 'test')->shouldReturn('test');
    }

    public function it_can_delete_a_queue()
    {
        $lua = $this->getLua('empty_queue');
        $this->adapter->evalLua($lua, ['test', 'test'])->shouldBeCalled();
        $this->deleteQueue('test');
    }

    public function it_can_purge_a_command()
    {
        $lua = $this->getLua('purge_message');
        $this->adapter->evalLua($lua, ['test', 'test', 'test'])->shouldBeCalled();
        $this->purgeCommand('test', 'test');
    }

    public function it_can_schedule_a_command()
    {
        $date = new \DateTime();
        $timestamp = $date->getTimestamp();
        $lua = $this->getLua('schedule_message');
        $this->adapter->evalLua($lua, ['test', 'test', 'test', 'test', $timestamp])->shouldBeCalled();
        $this->scheduleCommand('test', 'test', 'test', $date);
    }

    public function it_can_get_the_time_of_a_scheduled_command()
    {
        $timestamp = time();
        $this->adapter->zScore('test:schedule', 'test||test')->willReturn($timestamp);
        $date = $this->getScheduledTime('test', 'test');
        $date->getTimestamp()->shouldReturn($timestamp);
    }

    public function it_can_clear_the_schedule()
    {
        $lua = $this->getLua('clear_schedule');
        $this->adapter->evalLua($lua, ['test', 'test', '-inf', '+inf'])->shouldBeCalled();
        $this->clearSchedule(['test']);
    }

    public function it_can_receive_due_scheduled_commands()
    {
        $date = new \DateTime();
        $timestamp = $date->getTimestamp();
        $lua = $this->getLua('receive_due_messages');
        $this->adapter->evalLua($lua, ['test', 0, $timestamp, SchedulerWorker::DEFAULT_THROTTLE])
            ->shouldBeCalled()
            ->willReturn([['test', 'test', 'test', $timestamp]]);
        $commandsWrapper = $this->receiveDueCommands($date);
        $commands = $commandsWrapper->getWrappedObject();
        \PHPUnit_Framework_Assert::assertArrayHasKey(0, $commands);
        \PHPUnit_Framework_Assert::assertArrayNotHasKey(1, $commands);
        \PHPUnit_Framework_Assert::assertEquals('test', $commands[0]->getId());
    }

    public function it_can_purge_the_namespace()
    {
        $lua = $this->getLua('purge_namespace');
        $this->adapter->evalLua($lua, ['test'])->shouldBeCalled();
        $this->purgeNamespace();
    }

    private function getLua(string $script): string
    {
        return file_get_contents(RedisDriver::LUA_PATH . '/' . $script . '.lua');
    }
}
