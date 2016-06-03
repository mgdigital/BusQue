BusQue: Command Queue and Scheduler for PHP7
============================================

[![Build Status](https://travis-ci.org/mgdigital/BusQue.svg?branch=master)](https://travis-ci.org/mgdigital/BusQue)

I built BusQue because I found a lack of choice of simple message queues for medium-sized PHP applications.

The name BusQue signifies Command Bus + Message Queue. It was designed to be used in conjunction with [Tactician](https://github.com/thephpleague/tactician) and [Redis](http://redis.io/) using the [Predis](https://github.com/nrk/predis) client, along with a serializer such as PHP serialize(), [JMS Serializer](https://github.com/schmittjoh/serializer) or [MessagePack](https://github.com/rybakit/msgpack.php), but is open to replacement with alternate adapters.

One key feature I found missing in other queues is the ability to assign a unique ID to a job, allowing the same job to be queued multiple times but have it only execute once after the last insertion.

BusQue also allows scheduling of tasks.

[MGDigitalBusQueBundle](https://github.com/mgdigital/BusQueBundle) provides integration with the [Symfony](http://symfony.com/) framework.


Installation
------------

Install with composer:

    composer require mgdigital/busque
    
Or get the Symfony bundle:

    composer require mgdigital/busque-bundle


Usage
-----

To use BusQue you first need to instantiate an instance of `BusQue\Implementation` with its dependencies, or with the Symfony bundle you can get the `busque.implementation` service from the container.

Adapters for the following interfaces are available in this repository or you can write your own:
 
```php
<?php

use MGDigital\BusQue as BusQue;

$dependencies = [
    new BusQue\QueueNameResolverInterface,
    new BusQue\CommandSerializerInterface,
    new BusQue\CommandIdGeneratorInterface,
    new BusQue\QueueAdapterInterface,
    new BusQue\SchedulerAdapterInterface,
    new BusQue\ClockInterface,
    new BusQue\CommandBusAdapterInterface,
    new BusQue\ErrorHandlerInterface
];

$implementation = new BusQue\Implementation(...$dependencies); 
```   

The `BusQue\CommandHandler` class then needs to be registered with your command bus (Tactician). The Symfony bundle does this for you.

See [the Tactician website](https://tactician.thephpleague.com/) for further information on using a command bus.


### Queuing a command

`SendEmailCommand` is a command which you've configured Tactician to handle:

```php
<?php

$command = new SendEmailCommand('joe@example.com', 'Hello Joe!');
$commandBus->handle(new BusQue\QueuedCommand($command));
```


### Running a queue worker

```php
<?php

$worker = new BusQue\QueueWorker($implementation);
$worker->work('SendEmailCommand'); // Hello Joe!
```

Or in your Symfony app run `app/console busque:queue_worker SendEmailCommand`

*Tip:* If you want to see the commands being handled by the worker in the console, configure some logging middleware in Tactician, then run the `busque:queue_worker` command with the `--verbose` option.


### Scheduling a command

```php
<?php

$commandBus->handle(new BusQue\ScheduledCommand($command, new \DateTime('+1 minute')));
```


### Running the scheduler worker

Only one scheduler worker is needed to manage all queues. The scheduler worker's only job is to queue commands which are due. A queue worker must also be running to handle these commands.

```php
<?php

$schedulerWorker = new BusQue\SchedulerWorker($implementation);
$schedulerWorker->work(); // 1 minute later... Hello Joe!
```

Or in your Symfony app run `app/console busque:scheduler_worker`


### Commands needing an identifier

This command is queued every time the stock level of a product changes, but we give the command an ID:

```php
<?php

$productId = 123;
$command = new SyncStockLevelsWithExternalApiCommand($productId);

$uniqueCommandId = 'SyncStock' . $productId; 

$commandBus->handle(new BusQue\QueuedCommand($command, $uniqueCommandId));
```

When you don't specify a unique command ID, one will be generated automatically. You could also configure a custom ID generator for this type of command, Then a consistent ID would be generated wherever this command is issued from in your app.

What if the queue is busy and hasn't had time to process this command, before the stock level of this product changes a second time? The last thing we want is a duplicate of this message going into the queue, the stock level still only needs syncing once.

Because we identified the command by the product ID, it will only be allowed in the queue (or the scheduler) once at any given time.

Conversely, if you wanted to be able to issue the same command multiple times, and be sure the queue worker will run each copy of the command, you would have to ensure each copy of the command has a unique ID.


### Checking a command's progress

When we know the ID of a command and the name of its queue, we can also check its status:

```php
<?php

$queueName = $implementation->getQueueNameResolver()->resolveQueueName($command);
echo $implementation->getQueueAdapter()->getCommandStatus($queueName, $uniqueCommandId); // completed
```   


Tests
-----

Run the phpspec test suite:

    bin/phpspec run -f pretty

Or run the Behat acceptance suite to test the implementation in your symfony app.

The [busque.feature](https://raw.githubusercontent.com/mgdigital/BusQueBundle/master/Features/busque.feature) file describes BusQue's expected behaviour.

`/behat.yml`:

```yaml
default:
    suites:
        busque:
            type: symfony_bundle
            bundle: MGDigitalBusQueBundle
            contexts:
                - MGDigital\BusQueBundle\Features\Context\FeatureContext
```

then:

    bin/behat


Warnings
--------
- I've only just written this so there may be some gotchas that I haven't encountered yet. I intend to improve its resilience and expand it with further capabilities as time permits, and also welcome pull requests!
- I've not used this in production yet but I intend to soon! Good luck :)
