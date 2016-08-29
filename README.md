BusQue
======

[![Build Status](https://travis-ci.org/mgdigital/BusQue.svg?branch=master)](https://travis-ci.org/mgdigital/BusQue) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mgdigital/BusQue/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mgdigital/BusQue/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/mgdigital/BusQue/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mgdigital/BusQue/?branch=master) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/425b7104-519a-4292-abe2-bfebccee643e/small.png)](https://insight.sensiolabs.com/projects/425b7104-519a-4292-abe2-bfebccee643e)

### A flexible, modern command queue and scheduler for PHP7, built on Redis

I built BusQue because I found a lack of choice of simple message queues for medium-sized PHP applications.

The name BusQue signifies Command Bus + Message Queue. It was designed to be used in conjunction with [Tactician](https://github.com/thephpleague/tactician) and [Redis](http://redis.io/) using either the [PHPRedis](https://github.com/phpredis/phpredis) or [Predis](https://github.com/nrk/predis) clients, along with a serializer such as PHP serialize(), but is open to replacement with alternate adapters.

One key feature I found missing in other queues is the ability to assign a unique ID to a job, allowing the same job to be queued multiple times but have it only execute once after the last insertion.

[MGDigitalBusQueBundle](https://github.com/mgdigital/BusQueBundle) provides integration with the [Symfony](http://symfony.com/) framework.


Installation
------------

Install with composer:

    composer require mgdigital/busque

Or get the Symfony bundle:

    composer require mgdigital/busque-bundle

You'll also need a [Redis](http://redis.io/) server to run the queues on.


Usage
-----

To use BusQue you first need to instantiate an instance of `BusQue\Implementation` with its dependencies. A basic configuration could look something like this:

```php
<?php

use MGDigital\BusQue as BusQue;

// The preferred client is PHPRedis:
$client = new Redis();
$adapter = new BusQue\Redis\PHPRedis\PHPRedisAdapter($client);

// A Predis adepter is included, although Predis can have issues when used in long-running processes.
// $client = new Predis\Client();
// $adapter = new BusQue\Redis\Predis\PredisAdapter($client);

$driver = new BusQue\Redis\RedisDriver($adapter);

// The PHP serializer should fit most use cases:
$serializer = new BusQue\Serializer\PHPCommandSerializer();

// The MD5 generator creates an ID unique to the serialized command:
$idGenerator = new BusQue\IdGenerator\Md5IdGenerator($serializer);

$implementation = new BusQue\Implementation(
    // Puts all commands into the "default" queue:
    new BusQue\QueueResolver\SimpleQueueResolver('default'), 
    $serializer,
    $idGenerator,
    // The Redis driver is used as both the queue and scheduler:
    $driver,
    $driver,
    // Always returns the current time:
    new BusQue\SystemClock(),
    // Inject your command bus here:
    new BusQue\Tactician\CommandBusAdapter($commandBus),
    // Inject your logger here:
    new Psr\Log\NullLogger()
);

$busQue = new BusQue\BusQue($implementation);
```

The `BusQue\Handler\QueuedCommandHandler` and `BusQue\Handler\ScheduledCommandHandler` classes also needs to be registered with your command bus (Tactician). See [the Tactician website](https://tactician.thephpleague.com/) for further information on using a command bus.

If you're using the Symfony bundle, then all of the above is done for you, and you can just get the `busque` service from the container.


### Queuing a command

`SendEmailCommand` is a command which you've configured Tactician to handle:

```php
<?php

$command = new SendEmailCommand('joe@example.com', 'Hello Joe!');

$commandBus->handle(new BusQue\QueuedCommand($command));

// or

$busQue->queueCommand($command);
```


### Running a queue worker

```php
<?php

$busQue->workQueue('default'); // Hello Joe!
```

Or in your Symfony app run `app/console busque:queue_worker default`

You need to run at least one worker instance for each of your queues, using something like [supervisord](http://supervisord.org/).

*Tip:* If you want to see the commands being handled by the worker in the console, configure some logging middleware in Tactician, then run the `busque:queue_worker` command with the `--verbose` option.


### Scheduling a command

```php
<?php

$commandBus->handle(new BusQue\ScheduledCommand($command, new \DateTime('+1 minute')));

// or

$busQue->scheduleCommand($command, new \DateTime('+1 minute'));
```


### Running the scheduler worker

Only one scheduler worker is needed to manage the schedule for all queues. The scheduler worker's only job is to queue commands which are due. A queue worker must also be running to handle these commands.

```php
<?php

$busQue->workSchedule(); // 1 minute later... Hello Joe!
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

When you don't specify a unique command ID, one will be generated automatically.

What if the queue is busy and hasn't had time to process this command, before the stock level of this product changes a second time? The last thing we want is a duplicate of this message going into the queue, the stock level still only needs syncing once.

Because we identified the command by the product ID, it will only be allowed in the queue (or the scheduler) once at any given time.

Conversely, if you wanted to be able to issue the same command multiple times, and be sure the queue worker will run each copy of the command, you would have to ensure each copy of the command has a unique ID.

This behaviour works as follows:

- Only one command with the same ID may be queued or scheduled at one time
- If a command with the same ID is currently in progress, a new command with the same ID may be queued
- When the queue encounters a command whose ID is already in progress, the command will be re-inserted at the end of the queue
- When scheduling a command with an ID which is already scheduled, the originally scheduled command will be replaced with the newly scheduled command

Using the MD5IdGenerator will generate an ID consistently unique to the command and its payload. An alternate ID generator could be used if different behaviour is needed.


### Checking the length of a queue

We can also check the number of items in any queue:

```php
<?php

echo $busQue->getQueuedCount($queueName); // 0
```


### Listing queues

Queues are created automatically if they don't exist, using whichever queue name is returned from the `QueueResolverInterface` adapter. A worker can work on a queue which doesn't exist yet. You need to make sure that if a new queue name is generated, there is a worker to receive the commands in that queue.

```php
<?php

$queues = $busQue->listQueues(); // ['SendEmailCommand', 'SyncStockCommand']
```


### Cancelling a command

If you want to cancel a command for any reason, you can remove all trace of it with the following call:

```php
<?php

$busQue->purgeCommand($queueName, $uniqueCommandId);
```


### Clearing a queue

```php
<?php

$busQue->deleteQueue($queueName);
```


### Listing the IDs of commands currently in a queue

```php
<?php

$ids = $busQue->listQueuedIds($queueName); // ['command1id', 'command2id']
```


### Listing the IDs of commands currently in progress

```php
<?php

$ids = $busQue->listInProgressIds($queueName); // []
```


### Reading a command from the queue based on its ID

This method returns an unserialized command from BusQue based on its queue name and ID, leaving any messages in the queue untouched, and throwing a `BusQue\CommandNotFoundException` if the command was not found in the command store.

```php
<?php

$command = $busQue->getCommand($queueName, $uniqueCommandId);
```

*Further convenience methods can be found in the `BusQue\BusQue` class.*

Tests
-----

See the test suite output on Travis CI:

[![Build Status](https://travis-ci.org/mgdigital/BusQue.svg?branch=master)](https://travis-ci.org/mgdigital/BusQue)

Run the phpspec test suite:

    bin/phpspec run -f pretty

And run the Behat acceptance suite:

    bin/behat

By default the Behat suite will test integration with PHPRedis. Integration with Predis can also be tested:

    bin/behat --profile predis

These tests will attempt to write to a Redis instance at `redis://redis:6379` by default. You can configure an alternate test client by providing an alternate `FeatureContext` class extending either `BusQue\Features\Context\AbstractPHPRedisContext` or `BusQue\Features\Context\AbstractPredisContext`.


Docker
------

A basic docker environment is included for testing.

```sh

cd docker
docker-compose -f ./docker-compose.yml up
docker exec -ti busque-php composer install
docker exec -ti busque-php bin/behat
```

Warnings
--------
- I've only just written this so there may be some gotchas that I haven't encountered yet. The API is still subject to change as I iron out issues. I intend to improve its resilience and expand it with further capabilities as time permits, and also welcome pull requests!
- I've not used this in production yet but I intend to soon! Good luck :)
