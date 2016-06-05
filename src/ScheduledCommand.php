<?php

namespace MGDigital\BusQue;

final class ScheduledCommand implements BusQueCommandInterface
{

    private $command;
    private $dateTime;
    private $id;

    public function __construct($command, \DateTime $dateTime, string $id = null)
    {
        $this->command = $command;
        $this->dateTime = $dateTime;
        $this->id = $id;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getDateTime(): \DateTime
    {
        return $this->dateTime;
    }

    public function getId()
    {
        return $this->id;
    }
}
