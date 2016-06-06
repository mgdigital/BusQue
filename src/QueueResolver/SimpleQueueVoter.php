<?php

namespace MGDigital\BusQue\QueueResolver;

use MGDigital\BusQue\Exception\QueueResolverException;
use MGDigital\BusQue\QueueResolverInterface;

class SimpleQueueVoter implements QueueVoterInterface
{

    private $resolver;
    private $confidence;

    public function __construct(QueueResolverInterface $resolver, int $confidence = QueueVote::CONFIDENCE_NEUTRAL)
    {
        $this->resolver = $resolver;
        $this->confidence = $confidence;
    }

    public function getVote($command)
    {
        try {
            $queueName = $this->resolver->resolveQueueName($command);
            return new QueueVote($queueName, $this->confidence);
        } catch (QueueResolverException $e) {
            // Don't vote if the resolver cannot name a queue.
        }
    }
}
