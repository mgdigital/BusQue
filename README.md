BusQue: Command Queue and Scheduler for PHP7
============================================

[![Build Status](https://travis-ci.org/mgdigital/BusQue.svg?branch=master)](https://travis-ci.org/mgdigital/BusQue)

I built BusQue because I found a lack of choice of simple queueing solutions for medium-sized PHP applications.

It is built around a Command Bus architecture, hence the name BusQue (Coommand Bus + Message Queue).

One key feature I found missing in other queues is the ability to assign a unique ID to a job, allowing the same job to be queued multiple times but have it only execute once after the last insertion.

BusQue also allows scheduling of tasks.

BusQue was built with Redis in mind and currently has just one adapter for the Predis client, but it is possible to build drivers for other storage backends.

BusQue offers various options for command serialization, including PHP serialize(), JMS Serializer and MessagePack.
