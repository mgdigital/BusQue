<?php

namespace MGDigital\BusQue\Serializer;

use MessagePack\BufferUnpacker;
use MessagePack\Packer;
use MGDigital\BusQue\CommandSerializerInterface;

class MessagePackCommandSerializer implements CommandSerializerInterface
{

    private $packer;
    private $unpacker;

    public function __construct(Packer $packer, BufferUnpacker $unpacker)
    {
        $this->packer = $packer;
        $this->unpacker = $unpacker;
    }

    public function serialize($command): string
    {
        return $this->packer->pack($command);
    }

    public function unserialize(string $serialized)
    {
        return $this->unpacker->reset($serialized)->unpack();
    }
}
