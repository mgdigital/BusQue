<?php

namespace MGDigital\BusQue\Console;

use MGDigital\BusQue\SchedulerWorker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SchedulerWorkerCommand extends AbstractCommand
{

    protected function configure()
    {
        $this
            ->setName('busque:scheduler_worker')
            ->addOption('number', 'n', InputOption::VALUE_OPTIONAL, 'The number of commands to receive.', -1)
            ->addOption('time', 't', InputOption::VALUE_OPTIONAL, 'The time in seconds to run the worker', -1)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $number = $input->getOption('number');
        $time = $input->getOption('time');
        $worker = new SchedulerWorker($this->getImplementation());
        $worker->work($number, $time);
    }

}