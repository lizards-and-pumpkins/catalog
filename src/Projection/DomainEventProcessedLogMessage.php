<?php


namespace Brera\Projection;

use Brera\DomainEventHandler;
use Brera\Log\LogMessage;

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
}
