<?php

namespace LizardsAndPumpkins\Messaging\Event\Exception;

use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;

class DomainEventHandlerFailedMessage implements LogMessage
{
    /**
     * @var Message
     */
    private $domainEvent;

    /**
     * @var \Exception
     */
    private $exception;

    public function __construct(Message $domainEvent, \Exception $exception)
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
            "Failure during processing domain event \"%s\" with following message:\n%s",
            $this->domainEvent->getName(),
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
