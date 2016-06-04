<?php

namespace MGDigital\BusQue\Features\Context;

use MGDigital\BusQue\Predis\PredisAdapter;
use MGDigital\BusQue\QueueAdapterInterface;
use MGDigital\BusQue\SchedulerAdapterInterface;
use Predis\Client;

abstract class AbstractPredisContext extends AbstractQueueAndSchedulerContext
{

    private $predisAdapter;

    protected function getQueueAdapter(): QueueAdapterInterface
    {
        return $this->getPredisAdapter();
    }

    protected function getSchedulerAdapter(): SchedulerAdapterInterface
    {
        return $this->getPredisAdapter();
    }

    private function getPredisAdapter(): PredisAdapter
    {
        if ($this->predisAdapter === null) {
            $this->predisAdapter = new PredisAdapter($this->getPredisClient());
        }
        return $this->predisAdapter;
    }

    abstract protected function getPredisClient(): Client;

}