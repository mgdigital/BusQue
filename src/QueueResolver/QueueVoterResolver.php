<?php

namespace MGDigital\BusQue\QueueResolver;

use MGDigital\BusQue\Exception\QueueResolverException;
use MGDigital\BusQue\QueueResolverInterface;

class QueueVoterResolver implements QueueResolverInterface
{

    /**
     * @var QueueVoterInterface[]
     */
    private $voters = [ ];

    public function __construct(array $voters)
    {
        array_walk($voters, function (QueueVoterInterface $voter) {
            $this->voters[ ] = $voter;
        });
    }

    public function resolveQueueName($command): string
    {
        $votes = $this->getVotes($command);
        if (!isset($votes[ 0 ])) {
            throw new QueueResolverException;
        }
        return $votes[ 0 ]->getQueueName();
    }

    /**
     * @param mixed $command
     * @return QueueVote[]
     */
    public function getVotes($command): array
    {
        $votes = [ ];
        foreach ($this->voters as $voter) {
            $vote = $voter->getVote($command);
            if ($vote instanceof QueueVote) {
                $votes[ ] = $vote;
            }
        }
        usort($votes, function (QueueVote $voteA, QueueVote $voteB) {
            return $voteB->getConfidence() <=> $voteA->getConfidence();
        });
        return $votes;
    }
}
