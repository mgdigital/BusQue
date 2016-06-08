<?php

namespace MGDigital\BusQue\AutoQueue;

class ClassNameDecider implements DeciderInterface
{

    private $classNames;

    public function __construct(array $classNames)
    {
        $this->classNames = $classNames;
    }

    public function shouldBeQueued($command): bool
    {
        return is_object($command) && in_array(get_class($command), $this->classNames, true);
    }
}
