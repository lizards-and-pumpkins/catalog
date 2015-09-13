<?php


namespace Brera;

use Brera\Log\Writer\LogMessageWriter;
use Brera\Log\Writer\StdOutLogMessageWriter;
use Brera\Queue\File\FileQueue;
use Brera\Queue\LoggingQueueDecorator;
use Brera\Queue\Queue;
use Brera\Utils\Clearable;

class LoggingQueueFactory implements Factory
{
    use FactoryTrait;

    /**
     * @return Queue|Clearable
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
     * @return Queue|Clearable
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

    /**
     * @return LogMessageWriter
     */
    public function createLogMessageWriter()
    {
        return new StdOutLogMessageWriter();
    }
}
