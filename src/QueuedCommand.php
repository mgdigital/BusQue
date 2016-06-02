<?php

namespace MGDigital\BusQue;

final class QueuedCommand
{
    
    private $command;
    private $id;

    public function __construct($command, string $id = null)
    {
        $this->command = $command;
        $this->id = $id;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getId()
    {
        return $this->id;
    }

}