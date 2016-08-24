<?php

namespace MGDigital\BusQue\Serializer;

use MGDigital\BusQue\CommandSerializerInterface;
use MGDigital\BusQue\Exception\SerializerException;

class PHPCommandSerializer implements CommandSerializerInterface
{

    public function serialize($command): string
    {
        return serialize($command);
    }

    public function unserialize(string $serialized)
    {
        $unserialized = @unserialize($serialized);
        if (false === $unserialized) {
            throw new SerializerException();
        }
        return $unserialized;
    }
}
