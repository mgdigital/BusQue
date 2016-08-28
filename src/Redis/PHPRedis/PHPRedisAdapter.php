<?php

namespace MGDigital\BusQue\Redis\PHPRedis;

use MGDigital\BusQue\Exception\DriverException;
use MGDigital\BusQue\Redis\RedisAdapterInterface;

class PHPRedisAdapter implements RedisAdapterInterface
{

    private $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function bRPopLPush(string $source, string $destination, int $timeout)
    {
        return $this->tryCatch(function () use ($source, $destination, $timeout) {
            return $this->redis->brpoplpush($source, $destination, $timeout);
        });
    }

    public function hGet(string $key, string $field)
    {
        return $this->tryCatch(function () use ($key, $field) {
            return $this->redis->hGet($key, $field);
        });
    }

    public function sAdd(string $key, array $members)
    {
        $this->tryCatch(function () use ($key, $members) {
            $this->redis->sAdd($key, ...$members);
        });
    }

    public function sRem(string $key, array $members)
    {
        $this->tryCatch(function () use ($key, $members) {
            $this->redis->sRem($key, ...$members);
        });
    }

    public function sIsMember(string $key, string $value): bool
    {
        return $this->tryCatch(function () use ($key, $value) {
            return $this->redis->sIsMember($key, $value);
        });
    }

    public function sMembers(string $key): array
    {
        return $this->tryCatch(function () use ($key) {
            return $this->redis->sMembers($key);
        });
    }

    public function lLen(string $key): int
    {
        return $this->tryCatch(function () use ($key) {
            return $this->redis->lLen($key);
        });
    }

    public function lRange(string $key, int $offset = 0, int $limit = 10): array
    {
        return $this->tryCatch(function () use ($key, $offset, $limit) {
            return $this->redis->lRange($key, $offset, $limit);
        });
    }

    public function zScore(string $key, string $value)
    {
        $score = $this->tryCatch(function () use ($key, $value) {
            return $this->redis->zScore($key, $value);
        });
        return $score === null ? $score : intval($score);
    }

    public function evalLua(string $lua, array $args)
    {
        return $this->tryCatch(function () use ($lua, $args) {
            return $this->redis->evaluate($lua, $args, 0);
        });
    }

    private function tryCatch(callable $callable)
    {
        try {
            return $callable();
        } catch (\RedisException $e) {
            throw new DriverException($e->getMessage(), 0, $e);
        }
    }
}
