<?php

namespace MGDigital\BusQue\Tactician;

use League\Tactician\CommandBus;
use MGDigital\BusQue\CommandBusAdapterInterface;

class CommandBusAdapter implements CommandBusAdapterInterface
{

    private $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function handle($command, bool $fromQueue = false)
    {
        $this->commandBus->handle($command);
    }
}
