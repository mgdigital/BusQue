<?php

namespace MGDigital\BusQue\Features\Context;

use League\Tactician\CommandBus;
use MGDigital\BusQue\QueueResolver\ClassNameQueueResolver;
use MGDigital\BusQue\IdGenerator\ObjectHashIdGenerator;
use MGDigital\BusQue\Implementation;
use MGDigital\BusQue\Logging\LoggingErrorHandler;
use MGDigital\BusQue\QueueDriverInterface;
use MGDigital\BusQue\SchedulerDriverInterface;
use MGDigital\BusQue\Serializer\PHPCommandSerializer;
use MGDigital\BusQue\SystemClock;
use MGDigital\BusQue\Tactician\CommandBusAdapter;
use Psr\Log\NullLogger;

abstract class AbstractQueueAndSchedulerContext extends AbstractBaseContext
{

    protected function getImplementation(): Implementation
    {
        return new Implementation(
            new ClassNameQueueResolver(),
            new PHPCommandSerializer(),
            new ObjectHashIdGenerator(),
            $this->getQueueAdapter(),
            $this->getSchedulerAdapter(),
            new SystemClock(),
            new CommandBusAdapter(new CommandBus([])),
            new LoggingErrorHandler(new NullLogger())
        );
    }

    abstract protected function getQueueAdapter(): QueueDriverInterface;

    abstract protected function getSchedulerAdapter(): SchedulerDriverInterface;

}