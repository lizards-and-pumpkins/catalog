<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Log\Writer\LogMessageWriter;
use LizardsAndPumpkins\Log\Writer\StdOutLogMessageWriter;
use LizardsAndPumpkins\Queue\File\FileQueue;
use LizardsAndPumpkins\Queue\LoggingQueueDecorator;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\Utils\Clearable;

class LoggingQueueFactory implements Factory
{
    use FactoryTrait;

    /**
     * @return Queue|Clearable
     */
    public function createEventQueue()
    {
        $storageBasePath = $this->getMasterFactory()->getFileStorageBasePathConfig();
        $storagePath = $storageBasePath . '/lizards-and-pumpkins/event-queue/content';
        $lockFile = $storageBasePath . '/lizards-and-pumpkins/event-queue/lock';
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
        $storageBasePath = $this->getMasterFactory()->getFileStorageBasePathConfig();
        $storagePath = $storageBasePath . '/lizards-and-pumpkins/command-queue/content';
        $lockFile = $storageBasePath . '/lizards-and-pumpkins/command-queue/lock';
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
