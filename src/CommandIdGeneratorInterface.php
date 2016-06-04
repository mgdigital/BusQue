<?php

namespace MGDigital\BusQue;

interface CommandIdGeneratorInterface
{

    public function generateId($command): string;
}
