<?php

namespace MGDigital\BusQue\Features\Context;

class PHPRedisContext extends AbstractPHPRedisContext
{

    protected function getRedis(): \Redis
    {
        $redis = new \Redis();
        $redis->connect('busque-redis');
        return $redis;
    }
}
