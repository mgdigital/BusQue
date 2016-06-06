<?php

namespace MGDigital\BusQue;

class Implementation
{

    private $queueResolver;
    private $commandSerializer;
    private $commandIdGenerator;
    private $queueAdapter;
    private $schedulerAdapter;
    private $clock;
    private $commandBusAdapter;
    private $errorHandler;

    public function __construct(
        QueueResolverInterface $queueResolver,
        CommandSerializerInterface $commandSerializer,
        CommandIdGeneratorInterface $commandIdGenerator,
        QueueAdapterInterface $queueAdapter,
        SchedulerAdapterInterface $schedulerAdapter,
        ClockInterface $clock,
        CommandBusAdapterInterface $commandBusAdapter,
        ErrorHandlerInterface $errorHandler
    ) {
        $this->queueResolver = $queueResolver;
        $this->commandSerializer = $commandSerializer;
        $this->commandIdGenerator = $commandIdGenerator;
        $this->queueAdapter = $queueAdapter;
        $this->schedulerAdapter = $schedulerAdapter;
        $this->clock = $clock;
        $this->commandBusAdapter = $commandBusAdapter;
        $this->errorHandler = $errorHandler;
    }

    public function getQueueResolver(): QueueResolverInterface
    {
        return $this->queueResolver;
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
