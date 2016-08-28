<?php

namespace spec\MGDigital\BusQue;

use MGDigital\BusQue\ReceivedScheduledCommand;
use MGDigital\BusQue\SchedulerWorker;
use Prophecy\Argument;

final class SchedulerWorkerSpec extends AbstractSpec
{

    public function it_is_initializable()
    {
        $this->shouldHaveType(SchedulerWorker::class);
    }

    public function it_can_receive_and_queue_a_scheduled_command()
    {
        $dateTime = new \DateTime();
        $scheduledCommand = new ReceivedScheduledCommand('test_queue', 'test_id', 'serialized', $dateTime);
        $this->clock->getTime()->willReturn($dateTime);
        $this->schedulerDriver->receiveDueCommands($dateTime, 1, null, null)->willReturn([$scheduledCommand]);
        $this->queueDriver->queueCommand('test_queue', 'test_id', 'serialized')->shouldBeCalled();
        $this->logger->debug(Argument::type('string'), Argument::type('array'))->shouldBeCalled();
        $this->work(1, 1, 0);
    }
}
