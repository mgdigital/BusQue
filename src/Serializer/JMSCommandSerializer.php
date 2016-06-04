<?php

namespace MGDigital\BusQue\Serializer;

use JMS\Serializer\SerializerInterface;
use MGDigital\BusQue\CommandSerializerInterface;

class JMSCommandSerializer implements CommandSerializerInterface
{

    private $jmsSerializer;
    private $type;
    private $format;

    public function __construct(SerializerInterface $jmsSerializer, string $type, string $format = 'json')
    {
        $this->jmsSerializer = $jmsSerializer;
        $this->type = $type;
        $this->format = $format;
    }

    public function serialize($command): string
    {
        return $this->jmsSerializer->serialize($command, $this->format);
    }

    public function unserialize(string $serialized)
    {
        return $this->jmsSerializer->deserialize($serialized, $this->type, $this->format);
    }
}
