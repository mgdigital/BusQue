<?php

namespace spec\MGDigital\BusQue\QueueResolver;

use MGDigital\BusQue\QueueResolver\QueueVote;
use MGDigital\BusQue\QueueResolver\QueueVoterResolver;
use MGDigital\BusQue\QueueResolver\SimpleQueueResolver;
use MGDigital\BusQue\QueueResolver\SimpleQueueVoter;
use PhpSpec\ObjectBehavior;

class QueueVoterResolverSpec extends ObjectBehavior
{

    private $votes = [
        'queue1' => QueueVote::CONFIDENCE_LOW,
        'queue2' => QueueVote::CONFIDENCE_MEDIUM,
        'queue3' => QueueVote::CONFIDENCE_NEUTRAL,
    ];

    protected $expectedQueue = 'queue2';

    public function let()
    {
        $this->beConstructedWith(...$this->getConstructorArguments());
    }

    protected function getConstructorArguments(): array
    {
        $voters = [];
        foreach ($this->votes as $queueName => $confidence) {
            $resolver = new SimpleQueueResolver($queueName);
            $voters[] = new SimpleQueueVoter($resolver, $confidence);
        }
        return [$voters];
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(QueueVoterResolver::class);
    }

    public function it_resolves_the_correct_queue_name()
    {
        $this->resolveQueueName('test_queue')->shouldReturn($this->expectedQueue);
        $this->resolveQueueName('test_queue');
    }
}