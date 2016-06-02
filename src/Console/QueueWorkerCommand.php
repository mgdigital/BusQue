<?php

namespace MGDigital\BusQue\Console;

use MGDigital\BusQue\QueueWorker;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QueueWorkerCommand extends AbstractCommand
{

    protected function configure()
    {
        $this
            ->setName('busque:queue_worker')
            ->addArgument('queue', InputArgument::REQUIRED, 'The queue to work on.')
            ->addOption('number', null, InputOption::VALUE_OPTIONAL, 'The number of commands to receive.', null)
            ->addOption('time', null, InputOption::VALUE_OPTIONAL, 'The time in seconds to run the worker', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queueName = $input->getArgument('queue');
        $number = $input->getOption('number');
        $time = $input->getOption('time');
        $worker = new QueueWorker($this->getImplementation());
        $worker->work($queueName, $number, $time);
    }

}