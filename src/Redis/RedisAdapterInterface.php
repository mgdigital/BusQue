<?php

namespace MGDigital\BusQue\Redis;

interface RedisAdapterInterface
{

    public function bRPopLPush(string $source, string $destination, int $timeout);

    /**
     * @param string $key
     * @param string $field
     * @return string|null
     */
    public function hGet(string $key, string $field);

    public function sAdd(string $key, array $members);

    public function sRem(string $key, array $members);

    public function sIsMember(string $key, string $value): bool;

    public function sMembers(string $key): array;

    public function lLen(string $key): int;

    public function lRange(string $key, int $offset = 0, int $limit = 10): array;

    /**
     * @param string $key
     * @param string $value
     * @return int|null
     */
    public function zScore(string $key, string $value);

    public function evalLua(string $lua, array $args);
}
