<?php

namespace MGDigital\BusQue;

interface ErrorHandlerInterface
{

    public function handleCommandError($command, \Throwable $error);

    public function handleUnserializationError(
        string $queueName,
        string $commandId,
        string $serialized,
        \Throwable $error
    );
}
