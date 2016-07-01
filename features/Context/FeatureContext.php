<?php

namespace MGDigital\BusQue\Features\Context;

use MGDigital\BusQue\Predis\Client as BusQuePredisClient;
use Predis\Client;

class FeatureContext extends AbstractPredisContext
{

    public function getPredisClient(): Client
    {
        return new BusQuePredisClient();
    }

}
