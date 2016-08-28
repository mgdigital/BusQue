<?php

namespace spec\MGDigital\BusQue;

use MGDigital\BusQue\QueueWorker;
use MGDigital\BusQue\ReceivedCommand;
use Prophecy\Argument;

final class QueueWorkerSpec extends AbstractSpec
{

    public function it_is_initializable()
    {
        $this->shouldHaveType(QueueWorker::class);
    }

    public function it_can_receive_and_handle_a_queued_command()
    {
        $receivedCommand = new ReceivedCommand('test_queue', 'test_id', 'serialized');
        $this->queueDriver->awaitCommand('test_queue', null)->willReturn($receivedCommand);
        $this->commandSerializer->unserialize('serialized')->willReturn('test_command');
        $this->logger->debug(Argument::type('string'), Argument::type('array'))->shouldBeCalled();
        $this->commandBusAdapter->handle('test_command', true)->shouldBeCalled();
        $this->queueDriver->completeCommand('test_queue', 'test_id')->shouldBeCalled();
        $this->logger->info(Argument::type('string'), Argument::type('array'))->shouldBeCalled();
        $this->work('test_queue', 1);
    }

    public function it_can_handle_a_command_error()
    {
        $exception = new \Exception;
        $errorReceivedCommand = new ReceivedCommand('test_queue', 'error_id', 'error_serialized');
        $nextReceivedCommand = new ReceivedCommand('test_queue', 'next_id', 'serialized');
        $this->queueDriver->awaitCommand('test_queue', null)->willReturn($errorReceivedCommand, $nextReceivedCommand);
        $this->commandSerializer->unserialize('error_serialized')->willReturn('error_command');
        $this->commandSerializer->unserialize('serialized')->willReturn('next_command');
        $this->logger->debug(Argument::type('string'), Argument::type('array'))->shouldBeCalled();
        $this->commandBusAdapter->handle('error_command', true)->willThrow($exception);
        $this->commandBusAdapter->handle('next_command', true)->willReturn(null);
        $this->queueDriver->completeCommand('test_queue', 'error_id')->shouldBeCalled();
        $this->logger->error(Argument::type('string'), Argument::type('array'))->shouldBeCalled();
        $this->commandBusAdapter->handle('next_command', true)->shouldBeCalled();
        $this->queueDriver->completeCommand('test_queue', 'next_id')->shouldBeCalled();
        $this->work('test_queue', 2);
    }
}
