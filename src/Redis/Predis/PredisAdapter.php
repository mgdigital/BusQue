<?php

namespace MGDigital\BusQue\Redis\Predis;

use MGDigital\BusQue\Exception\RedisException;
use MGDigital\BusQue\Redis\RedisAdapterInterface;
use Predis\ClientInterface;
use Predis\Connection\ConnectionException as PredisConnectionException;

class PredisAdapter implements RedisAdapterInterface
{

    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function ping()
    {
        try {
            $this->client->ping();
        } catch (PredisConnectionException $e) {
            throw new RedisException();
        }
    }

    public function bRPopLPush(string $source, string $destination, int $timeout = null)
    {
        try {
            return $this->client->brpoplpush($source, $destination, $timeout);
        } catch (PredisConnectionException $e) {
            throw new RedisException();
        }
    }

    public function hGet(string $key, string $field)
    {
        try {
            return $this->client->hget($key, $field);
        } catch (PredisConnectionException $e) {
            throw new RedisException();
        }
    }

    public function sAdd(string $key, array $members)
    {
        try {
            $this->client->sadd($key, $members);
        } catch (PredisConnectionException $e) {
            throw new RedisException();
        }
    }

    public function sRem(string $key, array $members)
    {
        try {
            $this->client->srem($key, $members);
        } catch (PredisConnectionException $e) {
            throw new RedisException();
        }
    }

    public function sIsMember(string $key, string $value): bool
    {
        try {
            return $this->client->sismember($key, $value);
        } catch (PredisConnectionException $e) {
            throw new RedisException();
        }
    }

    public function sMembers(string $key): array
    {
        try {
            return $this->client->smembers($key);
        } catch (PredisConnectionException $e) {
            throw new RedisException();
        }
    }

    public function lLen(string $key): int
    {
        try {
            return $this->client->llen($key);
        } catch (PredisConnectionException $e) {
            throw new RedisException();
        }
    }

    public function lRange(string $key, int $offset = 0, int $limit = 10): array
    {
        try {
            return $this->client->lrange($key, $offset, $limit);
        } catch (PredisConnectionException $e) {
            throw new RedisException();
        }
    }

    public function zScore(string $key, string $value)
    {
        try {
            $score = $this->client->zscore($key, $value);
        } catch (PredisConnectionException $e) {
            throw new RedisException();
        }
        return $score === null ? $score : intval($score);
    }

    public function del(string $key)
    {
        try {
            $this->client->del($key);
        } catch (PredisConnectionException $e) {
            throw new RedisException();
        }
    }

    public function evalScript(string $path, array $args)
    {
        $command = new LuaFileCommand($path);
        $command->setArguments($args);
        return $this->client->executeCommand($command);
    }
}
