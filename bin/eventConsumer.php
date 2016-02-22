#!/usr/bin/env php
<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Projection\LoggingDomainEventHandlerFactory;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../../../autoload.php';
}


class EventConsumerWorker
{
    /**
     * @var SampleMasterFactory
     */
    private $factory;

    private function __construct()
    {
        $implementationFactory = new TwentyOneRunFactory();
        $commonFactory = new CommonFactory();
        
        $this->factory = new SampleMasterFactory();
        $this->factory->register($commonFactory);
        $this->factory->register($implementationFactory);
        $this->factory->register(new LoggingDomainEventHandlerFactory($commonFactory));
        $this->factory->register(new LoggingQueueFactory($implementationFactory));
    }

    public static function run()
    {
        $worker = new self();
        $eventConsumer = $worker->factory->createDomainEventConsumer();
        $eventConsumer->process();
    }
}

EventConsumerWorker::run();
