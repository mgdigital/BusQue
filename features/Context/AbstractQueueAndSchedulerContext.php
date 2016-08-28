<?php

namespace MGDigital\BusQue\Features\Context;

use League\Tactician\CommandBus;
use MGDigital\BusQue\IdGenerator\Md5IdGenerator;
use MGDigital\BusQue\QueueResolver\ClassNameQueueResolver;
use MGDigital\BusQue\Implementation;
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
        $serializer = new PHPCommandSerializer();
        $idGenerator = new Md5IdGenerator($serializer);
        return new Implementation(
            new ClassNameQueueResolver(),
            $serializer,
            $idGenerator,
            $this->getQueueAdapter(),
            $this->getSchedulerAdapter(),
            new SystemClock(),
            new CommandBusAdapter(new CommandBus([])),
            new NullLogger()
        );
    }

    abstract protected function getQueueAdapter(): QueueDriverInterface;

    abstract protected function getSchedulerAdapter(): SchedulerDriverInterface;
}
