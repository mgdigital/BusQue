<?php

namespace MGDigital\BusQue;

interface CommandSerializerInterface
{

    public function serialize($command): string;

    /**
     * @param string $serialized
     * @return mixed A command object which can be handled by the commandbus.
     */
    public function unserialize(string $serialized);
}
