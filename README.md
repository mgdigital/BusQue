BusQue: Command Queue and Scheduler for PHP7
============================================

[![Build Status](https://travis-ci.org/mgdigital/BusQue.svg?branch=master)](https://travis-ci.org/mgdigital/BusQue)

I built BusQue because I found a lack of choice of simple message queues for medium-sized PHP applications.

The name BusQue signifies Command Bus + Message Queue. It was designed to be used in conjunction with [Tactician](https://github.com/thephpleague/tactician) and [Redis](http://redis.io/), along with a serializer such as PHP serialize(), [JMS Serializer](https://github.com/schmittjoh/serializer) or [MessagePack](http://msgpack.org/), but is open to replacement with alternate adapters.

One key feature I found missing in other queues is the ability to assign a unique ID to a job, allowing the same job to be queued multiple times but have it only execute once after the last insertion.

BusQue also allows scheduling of tasks.

[MGDigitalBusQueBundle](https://github.com/mgdigital/BusQueBundle) provides integration with the [Symfony](http://symfony.com/) framework. If you want to use it outside of Symfony then you have to instantiate BusQue\Implementation with its dependencies.

Installation
------------

Install with composer:

    composer require mgdigital/busque
    
Or get the Symfony bundle:

    composer require mgdigital/busque-bundle

Examples
--------

    <?php
    
    use MGDigital\BusQue as BusQue;
    
    // Queueing a command:
    
    $command = new SendEmailCommand('joe@example.com', 'Hello Joe!'); // this is a command which you've configured your command bus to handle
    
    $commandBus->handle(new BusQue\QueuedCommand($command));
    
    $implementation = new BusQue\Implementation(...$dependencies); // or with the Symfony bundle $container->get('busque.implementation');
    
    $worker = new BusQue\QueueWorker($implementation);
    $worker->work('SendEmailCommand'); // Hello Joe!
    
    // or in your Symfony app run app/console busque:queue_worker SendEmailCommand
    
    
    // Scheduling a command
    
    $commandBus->handle(new BusQue\ScheduledCommand($command, new \DateTime('+1 minute')));
    
    $schedulerWorker = new BusQue\SchedulerWorker($implementation);
    $schedulerWorker->work();
    
    // or in your Symfony app run app/console busque:scheduler_worker
    
    // 1 minute later... Hello Joe!
    
    
    // Commands needing a unique ID:
    
    $productId = 123;
    $command = new SyncStockLevelsWithExternalApiCommand($productId);
    
    // This command is queued every time the stock level of a product changes, but we give the command a unique ID:
    $uniqueCommandId = 'stocksync' . $productId; // You could also use a custom ID generator to have unique IDs auto-generated for this type of command
    $commandBus->handle(new QueuedCommand($command, $uniqueCommandId));
    
    // What if the queue is busy and hasn't had time to process this command before the stock level changes a second time?
    // The last thing we want is a duplicate of this message going into the queue, the stock level still only needs syncing once.
    // Because we gave the command a unique ID it will only be executed once after its last insertion into the queue.
    

Tests
-----

Run the phpspec test suite:

    bin/phpspec run -f pretty

Or run the Behat acceptance suite to test the implementation in your symfony app:

/behat.yml:

    default:
        suites:
            busque:
                type: symfony_bundle
                bundle: MGDigitalBusQueBundle
                contexts:
                    - MGDigital\BusQueBundle\Features\Context\FeatureContext

then:

    bin/behat

Warnings
--------
- I've only just written this so there may be some gotchas that I haven't encountered yet. I intend to improve its resilience and expand it with further capabilities as time permits, and also welcome pull requests!
- I've not used this in production yet but I intend to soon! Good luck :)