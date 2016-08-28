<?php

namespace MGDigital\BusQue\Redis\Predis;

use MGDigital\BusQue\Exception\DriverException;
use MGDigital\BusQue\Redis\RedisAdapterInterface;
use Predis\ClientInterface;
use Predis\Connection\ConnectionException;

class PredisAdapter implements RedisAdapterInterface
{

    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function bRPopLPush(string $source, string $destination, int $timeout)
    {
        return $this->tryCatch(function () use ($source, $destination, $timeout) {
            return $this->client->brpoplpush($source, $destination, $timeout);
        });
    }

    public function hGet(string $key, string $field)
    {
        return $this->tryCatch(function () use ($key, $field) {
            return $this->client->hget($key, $field);
        });
    }

    public function sAdd(string $key, array $members)
    {
        $this->tryCatch(function () use ($key, $members) {
            $this->client->sadd($key, $members);
        });
    }

    public function sRem(string $key, array $members)
    {
        $this->tryCatch(function () use ($key, $members) {
            $this->client->srem($key, $members);
        });
    }

    public function sIsMember(string $key, string $value): bool
    {
        return $this->tryCatch(function () use ($key, $value) {
            return $this->client->sismember($key, $value);
        });
    }

    public function sMembers(string $key): array
    {
        return $this->tryCatch(function () use ($key) {
            return $this->client->smembers($key);
        });
    }

    public function lLen(string $key): int
    {
        return $this->tryCatch(function () use ($key) {
            return $this->client->llen($key);
        });
    }

    public function lRange(string $key, int $offset = 0, int $limit = 10): array
    {
        return $this->tryCatch(function () use ($key, $offset, $limit) {
            return $this->client->lrange($key, $offset, $limit);
        });
    }

    public function zScore(string $key, string $value)
    {
        $score = $this->tryCatch(function () use ($key, $value) {
            return $this->client->zscore($key, $value);
        });
        return $score === null ? $score : intval($score);
    }

    public function evalLua(string $lua, array $args)
    {
        return $this->tryCatch(function () use ($lua, $args) {
            return $this->client->eval($lua, 0, ...$args);
        });
    }

    private function tryCatch(callable $callable)
    {
        try {
            return $callable();
        } catch (ConnectionException $e) {
            throw new DriverException($e->getMessage(), 0, $e);
        }
    }
}
