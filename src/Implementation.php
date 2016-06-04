<?php

namespace MGDigital\BusQue;

final class Implementation
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

    public function setQueueNameResolver(QueueNameResolverInterface $queueNameResolver): Implementation
    {
        $implementation = clone $this;
        $implementation->queueNameResolver = $queueNameResolver;
        return $implementation;
    }

    public function getCommandSerializer(): CommandSerializerInterface
    {
        return $this->commandSerializer;
    }

    public function setCommandSerializer(CommandSerializerInterface $commandSerializer): Implementation
    {
        $implementation = clone $this;
        $implementation->commandSerializer = $commandSerializer;
        return $implementation;
    }

    public function getCommandIdGenerator(): CommandIdGeneratorInterface
    {
        return $this->commandIdGenerator;
    }

    public function setCommandIdGenerator(CommandIdGeneratorInterface $commandIdGenerator): Implementation
    {
        $implementation = clone $this;
        $implementation->commandIdGenerator = $commandIdGenerator;
        return $implementation;
    }

    public function getQueueAdapter(): QueueAdapterInterface
    {
        return $this->queueAdapter;
    }

    public function setQueueAdapter(QueueAdapterInterface $queueAdapter): Implementation
    {
        $implementation = clone $this;
        $implementation->queueAdapter = $queueAdapter;
        return $implementation;
    }

    public function getSchedulerAdapter(): SchedulerAdapterInterface
    {
        return $this->schedulerAdapter;
    }

    public function setSchedulerAdapter(SchedulerAdapterInterface $schedulerAdapter): Implementation
    {
        $implementation = clone $this;
        $implementation->schedulerAdapter = $schedulerAdapter;
        return $implementation;
    }

    public function getClock(): ClockInterface
    {
        return $this->clock;
    }

    public function setClock(ClockInterface $clock): Implementation
    {
        $implementation = clone $this;
        $implementation->clock = $clock;
        return $implementation;
    }

    public function getCommandBusAdapter(): CommandBusAdapterInterface
    {
        return $this->commandBusAdapter;
    }

    public function setCommandBusAdapter(CommandBusAdapterInterface $commandBusAdapter): Implementation
    {
        $implementation = clone $this;
        $implementation->commandBusAdapter = $commandBusAdapter;
        return $implementation;
    }

    public function getErrorHandler(): ErrorHandlerInterface
    {
        return $this->errorHandler;
    }

    public function setErrorHandler(ErrorHandlerInterface $errorHandler): Implementation
    {
        $implementation = clone $this;
        $implementation->errorHandler = $errorHandler;
        return $implementation;
    }
}
