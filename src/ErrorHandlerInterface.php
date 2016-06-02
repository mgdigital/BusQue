<?php

namespace MGDigital\BusQue;

interface ErrorHandlerInterface
{

    public function handle($command, \Throwable $error);

}