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
        try {
            return unserialize($serialized);
        } finally {
            restore_error_handler();
        }
    }

    private function handleError()
    {
        throw new SerializerException();
    }
}
