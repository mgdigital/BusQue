<?php

namespace MGDigital\BusQue;

interface ClockInterface
{

    public function getTime(): \DateTime;
}
