<?php

namespace MGDigital\BusQue\Features\Context;

use Behat\Behat\Context\SnippetAcceptingContext;
use MGDigital\BusQue\ClockInterface;
use MGDigital\BusQue\CommandBusAdapterInterface;
use MGDigital\BusQue\CommandIdGeneratorInterface;
use MGDigital\BusQue\Exception\TimeoutException;
use MGDigital\BusQue\Handler\QueuedCommandHandler;
use MGDigital\BusQue\Handler\ScheduledCommandHandler;
use MGDigital\BusQue\Implementation;
use MGDigital\BusQue\Logging\LoggingErrorHandler;
use MGDigital\BusQue\QueuedCommand;
use MGDigital\BusQue\QueueResolverInterface;
use MGDigital\BusQue\QueueWorker;
use MGDigital\BusQue\ScheduledCommand;
use MGDigital\BusQue\SchedulerWorker;
use Prophecy\Argument;
use Prophecy\Prophet;
use Psr\Log\NullLogger;

abstract class AbstractFeatureContext implements SnippetAcceptingContext
{

    /**
     * @var Implementation
     */
    protected $implementation;

    protected $prophet;
    protected $commandBus;
    protected $commandIdGenerator;
    protected $queueNameResolver;
    protected $clock;

    abstract protected function getImplementation(): Implementation;

    /**
     * @BeforeScenario
     */
    public function setup()
    {
        $this->prophet = new Prophet();
        $this->commandBus = $this->prophet->prophesize(CommandBusAdapterInterface::class);
        $this->commandIdGenerator = $this->prophet->prophesize(CommandIdGeneratorInterface::class);
        $this->commandIdGenerator->generateId(Argument::any())->willReturn('test_command_id');
        $this->queueNameResolver = $this->prophet->prophesize(QueueResolverInterface::class);
        $this->queueNameResolver->resolveQueueName(Argument::any())->willReturn('test_queue');
        $this->clock = $this->prophet->prophesize(ClockInterface::class);
        $implementation = $this->getImplementation();
        $this->implementation = new Implementation(
            $this->queueNameResolver->reveal(),
            $implementation->getCommandSerializer(),
            $this->commandIdGenerator->reveal(),
            $implementation->getQueueAdapter(),
            $implementation->getSchedulerAdapter(),
            $this->clock->reveal(),
            $this->commandBus->reveal(),
            new LoggingErrorHandler(new NullLogger())
        );
    }

    /**
     * @Given the queue is empty
     */
    public function theQueueIsEmpty()
    {
        $this->implementation->getQueueAdapter()->clearQueue('test_queue');
        $this->thereShouldBeNCommandsInTheQueue(0);
    }

    /**
     * @Then there should be :arg1 commands in the queue
     */
    public function thereShouldBeNCommandsInTheQueue(int $arg1)
    {
        $count = $this->implementation->getQueueAdapter()->getQueuedCount('test_queue');
        \PHPUnit_Framework_Assert::assertEquals($count, $arg1);
    }

    /**
     * @Given I queue :command
     */
    public function iQueueACommand($command)
    {
        $this->queueCommand($command);
    }

    /**
     * @Given I queue :command with ID :id
     */
    public function iQueueACommandWithId($command, string $id)
    {
        $this->queueCommand($command, $id);
    }

    protected function queueCommand($command, string $id = null)
    {
        $handler = new QueuedCommandHandler($this->implementation);
        $handler->handleQueuedCommand(new QueuedCommand($command, $id ?? 'test_command_id'));
    }

    /**
     * @Then :arg1 should be in the list of queued IDs
     */
    public function shouldBeInTheListOfQueuedIds($arg1)
    {
        $queuedIds = $this->implementation->getQueueAdapter()->getQueuedIds('test_queue');
        \PHPUnit_Framework_Assert::assertTrue(in_array($arg1, $queuedIds));
    }

    /**
     * @Given the command will throw an exception when it is handled
     */
    public function theCommandWillThrowAnExceptionWhenItIsHandled()
    {
        $this->commandBus->handle('test_command', true)->willThrow(new \Exception());
    }

    /**
     * @When I run the queue worker
     */
    public function iRunTheQueueWorker()
    {
        $worker = new QueueWorker($this->implementation);
        try {
            $worker->work('test_queue', 1, 1);
        } catch (TimeoutException $e) {}
    }

    /**
     * @Then the command should have run
     */
    public function theCommandShouldHaveRun()
    {
        $this->theCommandArgShouldHaveRun('test_command');
    }

    /**
     * @Then the command :arg1 should have run
     */
    public function theCommandArgShouldHaveRun($arg1)
    {
        $this->commandBus->handle($arg1, true)->shouldHaveBeenCalled();
    }

    /**
     * @Then the command should have a status of :arg1
     */
    public function theCommandShouldHaveAStatusOf($arg1)
    {
        $this->theCommandWithIdShouldHaveAStatusOf('test_command_id', $arg1);
    }

    /**
     * @Then the command with ID :arg1 should have a status of :arg2
     */
    public function theCommandWithIdShouldHaveAStatusOf($arg1, $arg2)
    {
        $status = $this->implementation->getQueueAdapter()->getCommandStatus('test_queue', $arg1);
        \PHPUnit_Framework_Assert::assertEquals($status, $arg2);
    }

    /**
     * @Then the command with ID :arg1 should resolve to :arg2
     */
    public function theCommandWithIdShouldResolveTo($arg1, $arg2)
    {
        $serialized = $this->implementation->getQueueAdapter()->readCommand('test_queue', $arg1);
        $command = $this->implementation->getCommandSerializer()->unserialize($serialized);
        \PHPUnit_Framework_Assert::assertEquals($command, $arg2);
    }

    /**
     * @Then I cancel :arg1
     */
    public function iCancel($arg1)
    {
        $this->implementation->getQueueAdapter()->purgeCommand('test_queue', $arg1);
    }

    /**
     * @Given I schedule :command to run at :arg1::arg2
     */
    public function iScheduleACommandToRunAt($command, $arg1, $arg2)
    {
        $this->iScheduleACommandWithIdToRunAt($command, 'test_command_id', $arg1, $arg2);
    }

    /**
     * @Given I schedule :command with ID :id to run at :arg1::arg2
     */
    public function iScheduleACommandWithIdToRunAt($command, $id, $arg1, $arg2)
    {
        $time = new \DateTime('@' . mktime($arg1, $arg2));
        $this->scheduleCommand($command, $time, $id);
    }

    protected function scheduleCommand($command, \DateTime $dateTime, string $id)
    {
        $hander = new ScheduledCommandHandler($this->implementation);
        $hander->handleScheduledCommand(new ScheduledCommand($command, $dateTime, $id));
    }

    /**
     * @Given the time is :arg1::arg2
     */
    public function theTimeIs($arg1, $arg2)
    {
        $this->clock->getTime()->willReturn(new \DateTime('@' . mktime($arg1, $arg2)));
    }

    /**
     * @When I run the scheduler worker
     */
    public function iRunTheSchedulerWorker()
    {
        $worker = new SchedulerWorker($this->implementation);
        try {
            $worker->work(null, 100, 0);
        } catch (TimeoutException $e) {}
    }

    /**
     * @When I clear the queue
     */
    public function iClearTheQueue()
    {
        $this->implementation->getQueueAdapter()->clearQueue('test_queue');
    }

    /**
     * @When I delete the queue
     */
    public function iDeleteTheQueue()
    {
        $this->implementation->getQueueAdapter()->deleteQueue('test_queue');
    }

    /**
     * @Then the queue should have been deleted
     */
    public function theQueueShouldHaveBeenDeleted()
    {
        $queueNames = $this->implementation->getQueueAdapter()->getQueueNames();
        \PHPUnit_Framework_Assert::assertFalse(in_array('test_queue', $queueNames));
    }

    /**
     * @When I clear the schedule
     */
    public function iClearTheSchedule()
    {
        $this->implementation->getSchedulerAdapter()->clearSchedule();
    }
}