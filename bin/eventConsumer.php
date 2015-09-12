#!/usr/bin/env php
<?php

namespace Brera;

use Brera\Log\Writer\FileLogMessageWriter;
use Brera\Log\Writer\LogMessageWriter;
use Brera\Queue\File\FileQueue;
use Brera\Queue\LoggingQueueDecorator;
use Brera\Queue\Queue;

require __DIR__ . '/../vendor/autoload.php';

$factory = new SampleMasterFactory();
$factory->register(new CommonFactory());
$factory->register(new SampleFactory());


class LoggingQueueFactory implements Factory
{
    use FactoryTrait;

    /**
     * @return Queue
     */
    public function createEventQueue()
    {
        $storagePath = sys_get_temp_dir() . '/brera/event-queue/content';
        $lockFile = sys_get_temp_dir() . '/brera/event-queue/lock';
        return new LoggingQueueDecorator(
            new FileQueue($storagePath, $lockFile),
            $this->getMasterFactory()->getLogger()
        );
    }

    /**
     * @return Queue
     */
    public function createCommandQueue()
    {
        $storagePath = sys_get_temp_dir() . '/brera/command-queue/content';
        $lockFile = sys_get_temp_dir() . '/brera/command-queue/lock';
        return new LoggingQueueDecorator(
            new FileQueue($storagePath, $lockFile),
            $this->getMasterFactory()->getLogger()
        );
    }
}

$eventConsumer = $factory->createDomainEventConsumer();
$eventConsumer->process();
