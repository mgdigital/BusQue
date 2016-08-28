<?php

namespace spec\MGDigital\BusQue\Redis\Predis;

use MGDigital\BusQue\Exception\DriverException;
use MGDigital\BusQue\Redis\Predis\PredisAdapter;
use PhpSpec\ObjectBehavior;
use Predis\Connection\ConnectionException as PredisException;

class PredisAdapterSpec extends ObjectBehavior
{

    /**
     * @var MockingClientInterface
     */
    private $client;

    /**
     * @var PredisException
     */
    private $predisException;

    public function let(MockingClientInterface $client, PredisException $predisException)
    {
        $this->beConstructedWith($client);
        $this->client = $client;
        $this->predisException = $predisException->getWrappedObject();
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(PredisAdapter::class);
    }

    public function it_can_brpoplpush()
    {
        $this->client->brpoplpush('test', 'test', 0)->shouldBeCalled()->willReturn('test');
        $result = $this->bRPopLPush('test', 'test', 0);
        \PHPUnit_Framework_Assert::assertEquals('test', $result->getWrappedObject());
        $this->client->brpoplpush('test', 'test', 0)->willThrow($this->predisException);
        $this->shouldThrow(DriverException::class)->during('bRPopLPush', ['test', 'test', 0]);
    }

    public function it_can_hget()
    {
        $this->client->hget('test', 'test')->shouldBeCalled()->willReturn('test');
        $this->hGet('test', 'test')->shouldReturn('test');
        $this->client->hget('test', 'test')->willThrow($this->predisException);
        $this->shouldThrow(DriverException::class)->during('hGet', ['test', 'test']);
    }

    public function it_can_sadd()
    {
        $this->client->sadd('test', ['test'])->shouldBeCalled();
        $this->sAdd('test', ['test']);
        $this->client->sadd('test', ['test'])->willThrow($this->predisException);
        $this->shouldThrow(DriverException::class)->during('sAdd', ['test', ['test']]);
    }

    public function it_can_srem()
    {
        $this->client->srem('test', ['test'])->shouldBeCalled();
        $this->sRem('test', ['test']);
        $this->client->srem('test', ['test'])->willThrow($this->predisException);
        $this->shouldThrow(DriverException::class)->during('sRem', ['test', ['test']]);
    }

    public function it_can_sismember()
    {
        $this->client->sismember('test', 'test')->shouldBeCalled()->willReturn(true);
        $this->sIsMember('test', 'test')->shouldReturn(true);
        $this->client->sismember('test', 'test')->willThrow($this->predisException);
        $this->shouldThrow(DriverException::class)->during('sIsMember', ['test', 'test']);
    }

    public function it_can_smembers()
    {
        $this->client->smembers('test')->shouldBeCalled()->willReturn(['test']);
        $this->sMembers('test')->shouldReturn(['test']);
        $this->client->smembers('test')->willThrow($this->predisException);
        $this->shouldThrow(DriverException::class)->during('sMembers', ['test']);
    }

    public function it_can_llen()
    {
        $this->client->llen('test')->shouldBeCalled()->willReturn(1);
        $this->lLen('test')->shouldReturn(1);
        $this->client->llen('test')->willThrow($this->predisException);
        $this->shouldThrow(DriverException::class)->during('lLen', ['test']);
    }

    public function it_can_lrange()
    {
        $this->client->lrange('test', 0, 10)->shouldBeCalled()->willReturn(['test']);
        $this->lRange('test', 0, 10)->shouldReturn(['test']);
        $this->client->lrange('test', 0, 10)->willThrow($this->predisException);
        $this->shouldThrow(DriverException::class)->during('lRange', ['test', 0, 10]);
    }

    public function it_can_zscore()
    {
        $this->client->zscore('test', 'test')->shouldBeCalled()->willReturn(1);
        $this->zScore('test', 'test')->shouldReturn(1);
        $this->client->zscore('test', 'test')->willThrow($this->predisException);
        $this->shouldThrow(DriverException::class)->during('zScore', ['test', 'test']);
    }
}
