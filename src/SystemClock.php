<?php

namespace MGDigital\BusQue;

class SystemClock implements ClockInterface
{

    public function getTime(): \DateTimeInterface
    {
        return new \DateTimeImmutable();
    }
}
