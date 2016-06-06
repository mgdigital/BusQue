<?php

namespace MGDigital\BusQue\QueueResolver;

class QueueVoterWithWhitelistResolver extends QueueVoterResolver
{

    private $whitelist;

    public function __construct(array $voters, array $whitelist)
    {
        parent::__construct($voters);
        $this->whitelist = $whitelist;
    }

    public function getVotes($command): array
    {
        $votes = [ ];
        foreach (parent::getVotes($command) as $vote) {
            if (in_array($vote->getQueueName(), $this->whitelist, true)) {
                $votes[ ] = $vote;
            }
        }
        return $votes;
    }
}
