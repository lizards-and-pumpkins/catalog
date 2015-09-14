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
        $storagePath = sys_get_temp_dir() . '/lizards-and-pumpkins/event-queue/content';
        $lockFile = sys_get_temp_dir() . '/lizards-and-pumpkins/event-queue/lock';
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
        $storagePath = sys_get_temp_dir() . '/lizards-and-pumpkins/command-queue/content';
        $lockFile = sys_get_temp_dir() . '/lizards-and-pumpkins/command-queue/lock';
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
