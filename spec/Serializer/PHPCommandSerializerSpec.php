<?php

namespace spec\MGDigital\BusQue\Serializer;

use MGDigital\BusQue\Exception\SerializerException;
use MGDigital\BusQue\Serializer\PHPCommandSerializer;
use PhpSpec\ObjectBehavior;

class PHPCommandSerializerSpec extends ObjectBehavior
{

    public function it_is_initializable()
    {
        $this->shouldHaveType(PHPCommandSerializer::class);
    }

    public function it_can_serialize_a_command()
    {
        $command = 'test';
        $this->serialize($command)->shouldReturn(serialize($command));
    }

    public function it_can_unserialize_a_command()
    {
        $command = 'test';
        $serialized = serialize($command);
        $this->unserialize($serialized)->shouldReturn($command);
    }

    public function it_can_handle_an_unserialization_error()
    {
        $this->shouldThrow(SerializerException::class)->during('unserialize', ['unserializable']);
    }
}
