<?php

namespace MGDigital\BusQue\Features\Context;

use MGDigital\BusQue\Redis\PHPRedis\PHPRedisAdapter;
use MGDigital\BusQue\Redis\RedisAdapterInterface;

abstract class AbstractPHPRedisContext extends AbstractRedisContext
{

    public function getRedisAdapter(): RedisAdapterInterface
    {
        return new PHPRedisAdapter($this->getRedis());
    }

    abstract protected function getRedis(): \Redis;
}
