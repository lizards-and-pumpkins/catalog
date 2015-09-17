#!/usr/bin/env php
<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Queue\Queue;
use League\CLImate\CLImate;
use LizardsAndPumpkins\Utils\BaseCliCommand;

require_once __DIR__ . '/../vendor/autoload.php';

class ReportQueueCount extends BaseCliCommand
{
    /**
     * @var SampleMasterFactory
     */
    private $factory;
    
    /**
     * @var CLImate
     */
    private $climate;

    private function __construct(MasterFactory $factory, CLImate $climate)
    {
        $this->factory = $factory;
        $this->climate = $climate;
        $this->setCLImate($climate);
    }

    /**
     * @return ReportQueueCount
     */
    public static function bootstrap()
    {
        $factory = new SampleMasterFactory();
        $factory->register(new CommonFactory());
        $factory->register(new SampleFactory());

        return new self($factory, new CLImate());
    }

    protected function execute(CLImate $cliimate)
    {
        $tableData = $this->formatTableData($this->factory->getCommandQueue(), $this->factory->getEventQueue());
        $this->climate->table($tableData);
    }

    /**
     * @param Queue $commandQueue
     * @param Queue $eventQueue
     * @return string[]
     */
    private function formatTableData(Queue $commandQueue, Queue $eventQueue)
    {
        return [
            [
                'Queue' => 'Command',
                'Count' => sprintf('%10d', $commandQueue->count())
            ],
            [
                'Queue' => 'Event',
                'Count' => sprintf('%10d', $eventQueue->count())
            ],
        ];
    }
}

ReportQueueCount::bootstrap()->run();
