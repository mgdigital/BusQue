<?php

namespace spec\MGDigital\BusQue\Redis\PHPRedis;

use MGDigital\BusQue\Exception\DriverException;
use MGDigital\BusQue\Redis\PHPRedis\PHPRedisAdapter;
use PhpSpec\ObjectBehavior;

class PHPRedisAdapterSpec extends ObjectBehavior
{

    /**
     * @var \Redis
     */
    private $redis;

    public function let(\Redis $redis)
    {
        $this->beConstructedWith($redis);
        $this->redis = $redis;
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(PHPRedisAdapter::class);
    }

    public function it_can_brpoplpush()
    {
        $this->redis->brpoplpush('test', 'test', 0)->shouldBeCalled()->willReturn('test');
        $result = $this->bRPopLPush('test', 'test', 0);
        \PHPUnit_Framework_Assert::assertEquals('test', $result->getWrappedObject());
        $this->redis->brpoplpush('test', 'test', 0)->willThrow(new \RedisException());
        $this->shouldThrow(DriverException::class)->during('bRPopLPush', ['test', 'test', 0]);
    }

    public function it_can_hget()
    {
        $this->redis->hGet('test', 'test')->shouldBeCalled()->willReturn('test');
        $this->hGet('test', 'test')->shouldReturn('test');
        $this->redis->hGet('test', 'test')->willThrow(new \RedisException());
        $this->shouldThrow(DriverException::class)->during('hGet', ['test', 'test']);
    }

    public function it_can_sadd()
    {
        $this->redis->sAdd('test', 'test')->shouldBeCalled();
        $this->sAdd('test', ['test']);
        $this->redis->sAdd('test', 'test')->willThrow(new \RedisException());
        $this->shouldThrow(DriverException::class)->during('sAdd', ['test', ['test']]);
    }

    public function it_can_srem()
    {
        $this->redis->srem('test', 'test')->shouldBeCalled();
        $this->sRem('test', ['test']);
        $this->redis->srem('test', 'test')->willThrow(new \RedisException());
        $this->shouldThrow(DriverException::class)->during('sRem', ['test', ['test']]);
    }

    public function it_can_sismember()
    {
        $this->redis->sismember('test', 'test')->shouldBeCalled()->willReturn(true);
        $this->sIsMember('test', 'test')->shouldReturn(true);
        $this->redis->sismember('test', 'test')->willThrow(new \RedisException());
        $this->shouldThrow(DriverException::class)->during('sIsMember', ['test', 'test']);
    }

    public function it_can_smembers()
    {
        $this->redis->sMembers('test')->shouldBeCalled()->willReturn(['test']);
        $this->sMembers('test')->shouldReturn(['test']);
        $this->redis->sMembers('test')->willThrow(new \RedisException());
        $this->shouldThrow(DriverException::class)->during('sMembers', ['test']);
    }

    public function it_can_llen()
    {
        $this->redis->lLen('test')->shouldBeCalled()->willReturn(1);
        $this->lLen('test')->shouldReturn(1);
        $this->redis->lLen('test')->willThrow(new \RedisException());
        $this->shouldThrow(DriverException::class)->during('lLen', ['test']);
    }

    public function it_can_lrange()
    {
        $this->redis->lrange('test', 0, 10)->shouldBeCalled()->willReturn(['test']);
        $this->lRange('test', 0, 10)->shouldReturn(['test']);
        $this->redis->lrange('test', 0, 10)->willThrow(new \RedisException());
        $this->shouldThrow(DriverException::class)->during('lRange', ['test', 0, 10]);
    }

    public function it_can_zscore()
    {
        $this->redis->zScore('test', 'test')->shouldBeCalled()->willReturn(1);
        $this->zScore('test', 'test')->shouldReturn(1);
        $this->redis->zScore('test', 'test')->willThrow(new \RedisException());
        $this->shouldThrow(DriverException::class)->during('zScore', ['test', 'test']);
    }

    public function it_can_eval_lua()
    {
        $this->redis->evaluate('test', ['test'], 0)->shouldBeCalled()->willReturn('test');
        $this->evalLua('test', ['test'])->shouldReturn('test');
        $this->redis->evaluate('test', ['test'], 0)->willThrow(new \RedisException());
        $this->shouldThrow(DriverException::class)->during('evalLua', ['test', ['test'], 1]);
    }
}
