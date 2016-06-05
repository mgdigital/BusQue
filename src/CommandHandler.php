<?php

namespace MGDigital\BusQue;

final class CommandHandler
{

    private $implementation;

    public function __construct(Implementation $implementation)
    {
        $this->implementation = $implementation;
    }

    public function handleQueued(QueuedCommand $queuedCommand)
    {
        list($queueName, $id, $serialized) = $this->process($queuedCommand);
        $this->implementation->getQueueAdapter()
            ->queueCommand($queueName, $id, $serialized);
    }

    public function handleScheduled(ScheduledCommand $scheduledCommand)
    {
        list($queueName, $id, $serialized) = $this->process($scheduledCommand);
        $this->implementation->getSchedulerAdapter()
            ->scheduleCommand($queueName, $id, $serialized, $scheduledCommand->getDateTime());
    }

    private function process(BusQueCommandInterface $command): array
    {
        $baseCommand = $command->getCommand();
        $queueName = $this->implementation->getQueueNameResolver()
            ->resolveQueueName($baseCommand);
        $commandId = $command->getId() ?: $this->implementation->getCommandIdGenerator()
                ->generateId($baseCommand);
        $serialized = $this->implementation->getCommandSerializer()
            ->serialize($baseCommand);
        return [ $queueName, $commandId, $serialized ];
    }
}
