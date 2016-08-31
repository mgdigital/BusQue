<?php

namespace MGDigital\BusQue;

class SystemClock implements ClockInterface
{

    public function getTime(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
