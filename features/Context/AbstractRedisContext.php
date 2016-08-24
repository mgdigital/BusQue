<?php

namespace MGDigital\BusQue\Features\Context;

use MGDigital\BusQue\Redis\RedisAdapterInterface;
use MGDigital\BusQue\Redis\RedisDriver;
use MGDigital\BusQue\QueueDriverInterface;
use MGDigital\BusQue\SchedulerDriverInterface;

abstract class AbstractRedisContext extends AbstractQueueAndSchedulerContext
{

    private $redisDriver;

    /**
     * @BeforeScenario
     */
    public function setup()
    {
        parent::setup();
        $this->getRedisDriver()->purgeNamespace();
    }

    protected function getQueueAdapter(): QueueDriverInterface
    {
        return $this->getRedisDriver();
    }

    protected function getSchedulerAdapter(): SchedulerDriverInterface
    {
        return $this->getRedisDriver();
    }

    private function getRedisDriver(): RedisDriver
    {
        if ($this->redisDriver === null) {
            $this->redisDriver = new RedisDriver($this->getRedisAdapter(), 'test');
        }
        return $this->redisDriver;
    }

    abstract protected function getRedisAdapter(): RedisAdapterInterface;
}
