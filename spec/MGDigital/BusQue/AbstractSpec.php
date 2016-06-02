<?php

namespace spec\MGDigital\BusQue;

use MGDigital\BusQue\ClockInterface;
use MGDigital\BusQue\CommandBusAdapterInterface;
use MGDigital\BusQue\CommandIdGeneratorInterface;
use MGDigital\BusQue\CommandSerializerInterface;
use MGDigital\BusQue\ErrorHandlerInterface;
use MGDigital\BusQue\Implementation;
use MGDigital\BusQue\QueueAdapterInterface;
use MGDigital\BusQue\QueueNameResolverInterface;
use MGDigital\BusQue\SchedulerAdapterInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

abstract class AbstractSpec extends ObjectBehavior
{

    protected $queueNameResolver;
    protected $commandSerializer;
    protected $commandIdGenerator;
    protected $queueAdapter;
    protected $schedulerAdapter;
    protected $clock;
    protected $commandBusAdapter;
    protected $errorHandler;
    protected $implementation;

    public function let(
        $queueNameResolver,
        $commandSerializer,
        $commandIdGenerator,
        $queueAdapter,
        $schedulerAdapter,
        $clock,
        $commandBusAdapter,
        $errorHandler,
        $implementation
    ) {

        $queueNameResolver->beADoubleOf(QueueNameResolverInterface::class);
        $queueNameResolver->resolveQueueName(Argument::any())->willReturn('test_queue');

        $commandSerializer->beADoubleOf(CommandSerializerInterface::class);
        $commandSerializer->serialize(Argument::any())->willReturn('test_serialized');

        $commandIdGenerator->beADoubleOf(CommandIdGeneratorInterface::class);
        $commandIdGenerator->generateId(Argument::any())->willReturn('test_generated_id');

        $queueAdapter->beADoubleOf(QueueAdapterInterface::class);

        $schedulerAdapter->beADoubleOf(SchedulerAdapterInterface::class);

        $clock->beADoubleOf(ClockInterface::class);

        $commandBusAdapter->beADoubleOf(CommandBusAdapterInterface::class);

        $errorHandler->beADoubleOf(ErrorHandlerInterface::class);

        $implementation->beADoubleOf(Implementation::class);
        $implementation->getQueueNameResolver()->willReturn($queueNameResolver);
        $implementation->getCommandSerializer()->willReturn($commandSerializer);
        $implementation->getCommandIdGenerator()->willReturn($commandIdGenerator);
        $implementation->getQueueAdapter()->willReturn($queueAdapter);
        $implementation->getSchedulerAdapter()->willReturn($schedulerAdapter);
        $implementation->getClock()->willReturn($clock);
        $implementation->getCommandBusAdapter()->willReturn($commandBusAdapter);
        $implementation->getErrorHandler()->willReturn($errorHandler);

        $this->queueNameResolver = $queueNameResolver;
        $this->commandSerializer = $commandSerializer;
        $this->commandIdGenerator = $commandIdGenerator;
        $this->queueAdapter = $queueAdapter;
        $this->schedulerAdapter = $schedulerAdapter;
        $this->clock = $clock;
        $this->commandBusAdapter = $commandBusAdapter;
        $this->errorHandler = $errorHandler;
        $this->implementation = $implementation;

        $this->beConstructedWith(...$this->getConstructorArguments());
    }

    protected function getConstructorArguments(): array
    {
        return [$this->implementation];
    }

}