<?php

namespace MGDigital\BusQue\AutoQueue;

interface DeciderInterface
{

    public function shouldBeQueued($command): bool;
}
