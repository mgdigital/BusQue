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

    public function handle($command, \Throwable $error)
    {
        $this->logger->error($error->getMessage(), [
            'command' => $command,
            'error' => $error,
        ]);
    }

}