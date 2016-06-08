<?php

namespace MGDigital\BusQue;

interface CommandBusAdapterInterface
{

    public function handle($command, bool $fromQueue = false);
}
