<?php

namespace MGDigital\BusQue\AutoQueue;

use MGDigital\BusQue\CommandBusAdapterInterface;

class AutoQueuingCommandBusAdapter implements CommandBusAdapterInterface, DeciderInterface
{

    private $baseCommandBus;
    private $fromQueueCommands = [];

    public function __construct(CommandBusAdapterInterface $baseCommandBus)
    {
        $this->baseCommandBus = $baseCommandBus;
    }

    public function handle($command, bool $fromQueue = false)
    {
        if ($fromQueue) {
            $hash = $this->getHash($command);
            $this->fromQueueCommands[$hash] = true;
        }
        $this->baseCommandBus->handle($command, $fromQueue);
        if (isset($hash)) {
            unset($this->fromQueueCommands[$hash]);
        }
    }

    public function shouldBeQueued($command): bool
    {
        $hash = $this->getHash($command);
        return !isset($this->fromQueueCommands[$hash]);
    }

    private function getHash($command): string
    {
        return md5(is_object($command) ? spl_object_hash($command) : (string) $command);
    }
}
