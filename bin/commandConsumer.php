#!/usr/bin/env php
<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Projection\LoggingCommandHandlerFactory;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../../../autoload.php';
}

class CommandConsumerWorker
{
    /**
     * @var SampleMasterFactory
     */
    private $factory;

    private function __construct()
    {
        $this->factory = new SampleMasterFactory();
        $commonFactory = new CommonFactory();
        $implementationFactory = new TwentyOneRunFactory();
        $this->factory->register($commonFactory);
        $this->factory->register($implementationFactory);
        //$this->enableDebugLogging($commonFactory, $implementationFactory);
    }

    private function enableDebugLogging(CommonFactory $commonFactory, TwentyOneRunFactory $implementationFactory)
    {
        $this->factory->register(new LoggingCommandHandlerFactory($commonFactory));
        $this->factory->register(new LoggingQueueFactory($implementationFactory));
    }

    public static function run()
    {
        $worker = new self();
        $commandConsumer = $worker->factory->createCommandConsumer();
        $commandConsumer->process();
    }
}

CommandConsumerWorker::run();
