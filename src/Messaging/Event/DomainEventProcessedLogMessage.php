<?php

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Logging\LogMessage;

class DomainEventProcessedLogMessage implements LogMessage
{
    /**
     * @var string
     */
    private $message;
    
    /**
     * @var DomainEventHandler
     */
    private $domainEventHandler;

    /**
     * @param string $message
     * @param DomainEventHandler $domainEventHandler
     */
    public function __construct($message, DomainEventHandler $domainEventHandler)
    {
        $this->message = $message;
        $this->domainEventHandler = $domainEventHandler;
    }
    
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->message;
    }

    /**
     * @return mixed[]
     */
    public function getContext()
    {
        return ['domain_event_handler' => $this->domainEventHandler];
    }

    /**
     * @return string
     */
    public function getContextSynopsis()
    {
        return sprintf('DomainEventHandler Class: %s', get_class($this->domainEventHandler));
    }
}
