<?php

namespace LizardsAndPumpkins\Messaging\Event\Exception;

use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;

class DomainEventHandlerFailedMessage implements LogMessage
{
    /**
     * @var DomainEvent
     */
    private $domainEvent;

    /**
     * @var \Exception
     */
    private $exception;

    public function __construct(DomainEvent $domainEvent, \Exception $exception)
    {
        $this->domainEvent = $domainEvent;
        $this->exception = $exception;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            "Failure during processing %s domain event with following message:\n%s",
            get_class($this->domainEvent),
            $this->exception->getMessage()
        );
    }

    /**
     * @return mixed[]
     */
    public function getContext()
    {
        return ['exception' => $this->exception];
    }

    /**
     * @return string
     */
    public function getContextSynopsis()
    {
        return sprintf('File: %s:%s', $this->exception->getFile(), $this->exception->getLine());
    }
}
