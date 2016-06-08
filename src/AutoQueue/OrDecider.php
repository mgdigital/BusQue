<?php

namespace MGDigital\BusQue\AutoQueue;

class OrDecider implements DeciderInterface
{

    /**
     * @var DeciderInterface[]
     */
    private $deciders = [];

    public function __construct(array $deciders)
    {
        array_walk($deciders, function (DeciderInterface $decider) {
            $this->deciders[] = $decider;
        });
    }

    public function shouldBeQueued($command): bool
    {
        foreach ($this->deciders as $decider) {
            if ($decider->shouldBeQueued($command)) {
                return true;
            }
        }
        return false;
    }
}
