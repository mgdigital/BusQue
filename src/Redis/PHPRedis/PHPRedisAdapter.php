<?php

namespace MGDigital\BusQue\Redis\PHPRedis;

use MGDigital\BusQue\Exception\RedisException;
use MGDigital\BusQue\Redis\RedisAdapterInterface;

class PHPRedisAdapter implements RedisAdapterInterface
{

    private $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function ping()
    {
        try {
            $this->redis->ping();
        } catch (\RedisException $e) {
            throw new RedisException();
        }
    }

    public function bRPopLPush(string $source, string $destination, int $timeout = null)
    {
        try {
            return $this->redis->brpoplpush($source, $destination, $timeout);
        } catch (\RedisException $e) {
            throw new RedisException();
        }
    }

    public function hGet(string $key, string $field)
    {
        try {
            return $this->redis->hGet($key, $field);
        } catch (\RedisException $e) {
            throw new RedisException();
        }
    }

    public function sAdd(string $key, array $members)
    {
        try {
            $this->redis->sAdd($key, ...$members);
        } catch (\RedisException $e) {
            throw new RedisException();
        }
    }

    public function sIsMember(string $key, string $value): bool
    {
        try {
            return $this->redis->sIsMember($key, $value);
        } catch (\RedisException $e) {
            throw new RedisException();
        }
    }

    public function sMembers(string $key): array
    {
        try {
            return $this->redis->sMembers($key);
        } catch (\RedisException $e) {
            throw new RedisException();
        }
    }

    public function lLen(string $key): int
    {
        try {
            return $this->redis->lLen($key);
        } catch (\RedisException $e) {
            throw new RedisException();
        }
    }

    public function lRange(string $key, int $offset = 0, int $limit = 10): array
    {
        try {
            return $this->redis->lRange($key, $offset, $limit);
        } catch (\RedisException $e) {
            throw new RedisException();
        }
    }

    public function zScore(string $key, string $value)
    {
        try {
            $score = $this->redis->zScore($key, $value);
        } catch (\RedisException $e) {
            throw new RedisException();
        }
        return $score === null ? $score : intval($score);
    }

    public function del(string $key)
    {
        try {
            $this->redis->del($key);
        } catch (\RedisException $e) {
            throw new RedisException();
        }
    }

    public function evalScript(string $path, array $args)
    {
        $lua = file_get_contents($path);
        try {
            return $this->redis->eval($lua, $args);
        } catch (\RedisException $e) {
            throw new RedisException();
        }
    }
}
