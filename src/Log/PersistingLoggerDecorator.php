<?php


namespace Brera\Log;

use Brera\Log\Persister\LogMessagePersister;

class PersistingLoggerDecorator implements Logger
{
    /**
     * @var Logger
     */
    private $component;
    
    /**
     * @var LogMessagePersister
     */
    private $logPersister;

    public function __construct(Logger $component, LogMessagePersister $logPersister)
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
