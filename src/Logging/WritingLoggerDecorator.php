<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

class WritingLoggerDecorator implements Logger
{
    /**
     * @var Logger
     */
    private $component;
    
    /**
     * @var LogMessageWriter
     */
    private $logWriter;

    public function __construct(Logger $component, LogMessageWriter $logWriter)
    {
        $this->component = $component;
        $this->logWriter = $logWriter;
    }
    
    public function log(LogMessage $message)
    {
        $this->logWriter->write($message);
        $this->component->log($message);
    }

    /**
     * @return LogMessage[]
     */
    public function getMessages() : array
    {
        return $this->component->getMessages();
    }
}
