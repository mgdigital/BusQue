<?php

namespace MGDigital\BusQue\Tactician;

use League\Tactician\Handler\MethodNameInflector\MethodNameInflector;

class ChainedInflector implements MethodNameInflector
{

    /**
     * @var MethodNameInflector[]
     */
    private $inflectors = [];

    /**
     * @param MethodNameInflector[] $inflectors
     */
    public function __construct(array $inflectors)
    {
        array_walk($inflectors, function (MethodNameInflector $inflector) {
            $this->inflectors[] = $inflector;
        });
    }

    public function inflect($command, $handler)
    {
        foreach ($this->inflectors as $inflector) {
            $methodName = $inflector->inflect($command, $handler);
            if (is_callable([$handler, $methodName])) {
                return $methodName;
            }
        }
        throw new \RuntimeException('None of the inflectors could guess the handler method name.');
    }
}
