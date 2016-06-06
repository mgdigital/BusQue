<?php

namespace spec\MGDigital\BusQue\QueueResolver;

use MGDigital\BusQue\QueueResolver\QueueVoterWithWhitelistResolver;

class QueueVoterWithWhitelistResolverSpec extends QueueVoterResolverSpec
{

    protected $whitelist = [
        'queue1',
        'queue3',
    ];

    protected $expectedQueue = 'queue3';

    public function it_is_initializable()
    {
        $this->shouldHaveType(QueueVoterWithWhitelistResolver::class);
    }

    protected function getConstructorArguments(): array
    {
        $args = parent::getConstructorArguments();
        $args[] = $this->whitelist;
        return $args;
    }
}