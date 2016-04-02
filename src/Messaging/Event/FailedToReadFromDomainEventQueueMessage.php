<?php

namespace LizardsAndPumpkins\Messaging\Event;

use LizardsAndPumpkins\Logging\LogMessage;

class FailedToReadFromDomainEventQueueMessage implements LogMessage
{
    /**
     * @var \Exception
     */
    private $exception;

    public function __construct(\Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            "Failed to read from domain event queue message with following exception:\n\n%s",
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
        return sprintf('File: %s:%d', $this->exception->getFile(), $this->exception->getLine());
    }
}
