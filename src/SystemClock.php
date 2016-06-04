<?php

namespace MGDigital\BusQue;

class SystemClock implements ClockInterface
{

    public function getTime(): \DateTime
    {
        return new \DateTime();
    }
}
