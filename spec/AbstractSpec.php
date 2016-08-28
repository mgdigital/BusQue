<?php

namespace spec\MGDigital\BusQue;

use MGDigital\BusQue\ClockInterface;
use MGDigital\BusQue\CommandBusAdapterInterface;
use MGDigital\BusQue\CommandIdGeneratorInterface;
use MGDigital\BusQue\CommandSerializerInterface;
use MGDigital\BusQue\Implementation;
use MGDigital\BusQue\QueueDriverInterface;
use MGDigital\BusQue\QueueResolverInterface;
use MGDigital\BusQue\SchedulerDriverInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

abstract class AbstractSpec extends ObjectBehavior
{

    /**
     * @var QueueResolverInterface
     */
    protected $queueResolver;

    /**
     * @var CommandSerializerInterface
     */
    protected $commandSerializer;

    /**
     * @var CommandIdGeneratorInterface
     */
    protected $commandIdGenerator;

    /**
     * @var QueueDriverInterface
     */
    protected $queueDriver;

    /**
     * @var SchedulerDriverInterface
     */
    protected $schedulerDriver;

    /**
     * @var ClockInterface
     */
    protected $clock;

    /**
     * @var CommandBusAdapterInterface
     */
    protected $commandBusAdapter;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Implementation
     */
    protected $implementation;

    public function let(
        $queueResolver,
        $commandSerializer,
        $commandIdGenerator,
        $queueDriver,
        $schedulerDriver,
        $clock,
        $commandBusAdapter,
        $logger
    ) {

        $queueResolver->beADoubleOf(QueueResolverInterface::class);
        $queueResolver->resolveQueueName(Argument::any())->willReturn('test_queue');

        $commandSerializer->beADoubleOf(CommandSerializerInterface::class);
        $commandSerializer->serialize(Argument::any())->willReturn('serialized');

        $commandIdGenerator->beADoubleOf(CommandIdGeneratorInterface::class);
        $commandIdGenerator->generateId(Argument::any())->willReturn('test_generated_id');

        $queueDriver->beADoubleOf(QueueDriverInterface::class);

        $schedulerDriver->beADoubleOf(SchedulerDriverInterface::class);

        $clock->beADoubleOf(ClockInterface::class);

        $commandBusAdapter->beADoubleOf(CommandBusAdapterInterface::class);

        $logger->beADoubleOf(LoggerInterface::class);

        $implementation = new Implementation(
            $queueResolver->getWrappedObject(),
            $commandSerializer->getWrappedObject(),
            $commandIdGenerator->getWrappedObject(),
            $queueDriver->getWrappedObject(),
            $schedulerDriver->getWrappedObject(),
            $clock->getWrappedObject(),
            $commandBusAdapter->getWrappedObject(),
            $logger->getWrappedObject()
        );

        $this->queueResolver = $queueResolver;
        $this->commandSerializer = $commandSerializer;
        $this->commandIdGenerator = $commandIdGenerator;
        $this->queueDriver = $queueDriver;
        $this->schedulerDriver = $schedulerDriver;
        $this->clock = $clock;
        $this->commandBusAdapter = $commandBusAdapter;
        $this->logger = $logger;
        $this->implementation = $implementation;

        $this->beConstructedWith(...$this->getConstructorArguments());
    }

    protected function getConstructorArguments(): array
    {
        return [$this->implementation];
    }
}
