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
        set_error_handler([$this, 'handleError']);
        $unserialized = unserialize($serialized);
        restore_error_handler();
        return $unserialized;
    }

    private function handleError()
    {
        throw new SerializerException();
    }
}
