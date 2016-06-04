<?php

namespace MGDigital\BusQue\Features\Context;

use Predis\Client;

class FeatureContext extends AbstractPredisContext
{

    public function getPredisClient(): Client
    {
        return new Client();
    }

}