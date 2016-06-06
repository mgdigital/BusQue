<?php

namespace MGDigital\BusQue;

class Implementation
{

    private $queueNameResolver;
    private $commandSerializer;
    private $commandIdGenerator;
    private $queueAdapter;
    private $schedulerAdapter;
    private $clock;
    private $commandBusAdapter;
    private $errorHandler;

    public function __construct(
        QueueNameResolverInterface $queueNameResolver,
        CommandSerializerInterface $commandSerializer,
        CommandIdGeneratorInterface $commandIdGenerator,
        QueueAdapterInterface $queueAdapter,
        SchedulerAdapterInterface $schedulerAdapter,
        ClockInterface $clock,
        CommandBusAdapterInterface $commandBusAdapter,
        ErrorHandlerInterface $errorHandler
    ) {
        $this->queueNameResolver = $queueNameResolver;
        $this->commandSerializer = $commandSerializer;
        $this->commandIdGenerator = $commandIdGenerator;
        $this->queueAdapter = $queueAdapter;
        $this->schedulerAdapter = $schedulerAdapter;
        $this->clock = $clock;
        $this->commandBusAdapter = $commandBusAdapter;
        $this->errorHandler = $errorHandler;
    }

    public function getQueueNameResolver(): QueueNameResolverInterface
    {
        return $this->queueNameResolver;
    }

    public function getCommandSerializer(): CommandSerializerInterface
    {
        return $this->commandSerializer;
    }

    public function getCommandIdGenerator(): CommandIdGeneratorInterface
    {
        return $this->commandIdGenerator;
    }

    public function getQueueAdapter(): QueueAdapterInterface
    {
        return $this->queueAdapter;
    }

    public function getSchedulerAdapter(): SchedulerAdapterInterface
    {
        return $this->schedulerAdapter;
    }

    public function getClock(): ClockInterface
    {
        return $this->clock;
    }

    public function getCommandBusAdapter(): CommandBusAdapterInterface
    {
        return $this->commandBusAdapter;
    }

    public function getErrorHandler(): ErrorHandlerInterface
    {
        return $this->errorHandler;
    }
}
