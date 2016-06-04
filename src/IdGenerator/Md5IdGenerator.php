<?php

namespace MGDigital\BusQue\IdGenerator;

use MGDigital\BusQue\CommandIdGeneratorInterface;
use MGDigital\BusQue\CommandSerializerInterface;

class Md5IdGenerator implements CommandIdGeneratorInterface
{

    private $serializer;

    public function __construct(CommandSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function generateId($command): string
    {
        return md5($this->serializer->serialize($command));
    }
}
