<?php

namespace MGDigital\BusQue\Serializer;

use JMS\Serializer\Exception as JMSException;
use JMS\Serializer\SerializerInterface;
use MGDigital\BusQue\CommandSerializerInterface;
use MGDigital\BusQue\Exception\SerializerException;

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
        try {
            return $this->jmsSerializer->serialize($command, $this->format);
        } catch (JMSException $e) {
            throw new SerializerException($e->getMessage(), 0, $e);
        }
    }

    public function unserialize(string $serialized)
    {
        try {
            return $this->jmsSerializer->deserialize($serialized, $this->type, $this->format);
        } catch (JMSException $e) {
            throw new SerializerException($e->getMessage(), 0, $e);
        }
    }
}
