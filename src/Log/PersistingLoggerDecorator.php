<?php


namespace Brera\Log;

use Brera\Log\Writer\LogMessageWriter;

class PersistingLoggerDecorator implements Logger
{
    /**
     * @var Logger
     */
    private $component;
    
    /**
     * @var LogMessageWriter
     */
    private $logPersister;

    public function __construct(Logger $component, LogMessageWriter $logPersister)
    {
        $this->component = $component;
        $this->logPersister = $logPersister;
    }
    
    public function log(LogMessage $message)
    {
        $this->logPersister->persist($message);
        $this->component->log($message);
    }

    /**
     * @return LogMessage[]
     */
    public function getMessages()
    {
        return $this->component->getMessages();
    }
}
