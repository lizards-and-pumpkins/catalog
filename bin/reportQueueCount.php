#!/usr/bin/env php
<?php

namespace Brera;

use Brera\Queue\Queue;
use League\CLImate\CLImate;

require_once __DIR__ . '/../vendor/autoload.php';

class ReportQueueCount
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
    
    public function run()
    {
        try {
            $this->execute();
        } catch (\Exception $e) {
            $this->climate->error($e->getMessage());
            $this->climate->error(sprintf('%s:%d', $e->getFile(), $e->getLine()));
            $this->climate->usage();
        }
    }

    private function execute()
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
