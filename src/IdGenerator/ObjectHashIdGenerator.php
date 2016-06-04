<?php

namespace MGDigital\BusQue\IdGenerator;

use MGDigital\BusQue\CommandIdGeneratorInterface;

class ObjectHashIdGenerator implements CommandIdGeneratorInterface
{

    public function generateId($command): string
    {
        return spl_object_hash($command);
    }
}
