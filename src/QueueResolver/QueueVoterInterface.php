<?php

namespace MGDigital\BusQue\QueueResolver;

interface QueueVoterInterface
{

    /**
     * @param mixed $command
     * @return QueueVote|null
     */
    public function getVote($command);
}
