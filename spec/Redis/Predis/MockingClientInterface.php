<?php

namespace spec\MGDigital\BusQue\Redis\Predis;

use Predis\ClientInterface;

interface MockingClientInterface extends ClientInterface
{

    public function brpoplpush($source, $destination, $timeout);

    public function hget($key, $field);

    public function llen($key);

    public function lrange($key, $start, $stop);

    public function ping($message = null);

    public function sadd($key, array $members);

    public function sismember($key, $member);

    public function smembers($key);

    public function srem($key, $member);

    public function zscore($key, $member);
}
