<?php

namespace MGDigital\BusQue\Serializer;

use MGDigital\BusQue\CommandSerializerInterface;

class PHPCommandSerializer implements CommandSerializerInterface
{

    public function serialize($command): string
    {
        return serialize($command);
    }

    public function unserialize(string $serialized)
    {
        return unserialize($serialized);
    }
}
