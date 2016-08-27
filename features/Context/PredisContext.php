<?php

namespace MGDigital\BusQue\Features\Context;

use Predis\Client;
use Predis\ClientInterface;

class PredisContext extends AbstractPredisContext
{

    protected function getClient(): ClientInterface
    {
        return new Client([
            'scheme' => 'tcp',
            'host'   => 'redis',
            'port'   => 6379,
        ]);
    }
}
