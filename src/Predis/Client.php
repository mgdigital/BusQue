<?php

namespace MGDigital\BusQue\Predis;

use Predis\Client as BaseClient;

/**
 * {@inheritdoc}
 */
class Client extends BaseClient
{

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        if (!$this->getConnection() instanceof \Traversable) {
            return new \ArrayIterator([$this]);
        }
        return parent::getIterator();
    }
}
