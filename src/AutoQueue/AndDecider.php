<?php

namespace MGDigital\BusQue\AutoQueue;

class AndDecider implements DeciderInterface
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
            if (!$decider->shouldBeQueued($command)) {
                return false;
            }
        }
        return true;
    }
}
