<?php

namespace MGDigital\BusQue\Features\Context;

use MGDigital\BusQue\Redis\Predis\Client as BusQuePredisClient;
use Predis\ClientInterface;

class PredisContext extends AbstractPredisContext
{

    protected function getClient(): ClientInterface
    {
        return new BusQuePredisClient([
            'scheme' => 'tcp',
            'host'   => 'busque-redis',
            'port'   => 6379,
        ]);
    }
}
