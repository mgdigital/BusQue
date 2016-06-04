<?php

namespace MGDigital\BusQue\Console;

use MGDigital\BusQue\Implementation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractCommand extends Command
{

    protected $container;
    private $implementationId;

    public function __construct(ContainerInterface $container, string $implementationId = 'busque.implementation')
    {
        parent::__construct();
        $this->container = $container;
        $this->implementationId = $implementationId;
    }

    protected function getImplementation(): Implementation
    {
        return $this->container->get($this->implementationId);
    }
}
