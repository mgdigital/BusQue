<?php

namespace MGDigital\BusQue\Logging;

use MGDigital\BusQue\ErrorHandlerInterface;
use Psr\Log\LoggerInterface;

class LoggingErrorHandler implements ErrorHandlerInterface
{

    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handleCommandError($command, \Throwable $error)
    {
        $this->logger->error($error->getMessage(), [
            'command' => $command,
            'error' => $error,
        ]);
    }

    public function handleUnserializationError(
        string $queueName,
        string $commandId,
        string $serialized,
        \Throwable $error
    ) {
        $this->logger->error($error->getMessage(), compact('queueName', 'commandId', 'serialized', 'error'));
    }
}
