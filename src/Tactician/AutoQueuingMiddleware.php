<?php

namespace MGDigital\BusQue\Tactician;

use League\Tactician\Middleware;
use MGDigital\BusQue\AutoQueue\DeciderInterface;
use MGDigital\BusQue\BusQueCommandInterface;
use MGDigital\BusQue\QueuedCommand;

class AutoQueuingMiddleware implements Middleware
{

    private $decider;

    public function __construct(DeciderInterface $decider)
    {
        $this->decider = $decider;
    }

    public function execute($command, callable $next)
    {
        if (!$command instanceof BusQueCommandInterface && $this->decider->shouldBeQueued($command)) {
            $command = new QueuedCommand($command);
        }
        return $next($command);
    }
}
