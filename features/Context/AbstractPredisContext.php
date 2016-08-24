<?php

namespace MGDigital\BusQue\Features\Context;

use MGDigital\BusQue\Redis\Predis\PredisAdapter;
use MGDigital\BusQue\Redis\RedisAdapterInterface;
use Predis\ClientInterface;

abstract class AbstractPredisContext extends AbstractRedisContext
{

    public function getRedisAdapter(): RedisAdapterInterface
    {
        return new PredisAdapter($this->getClient());
    }

    abstract protected function getClient(): ClientInterface;
}
