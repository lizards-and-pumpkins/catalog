<?php

namespace Brera;

class FailedToReadFromDomainEventQueueMessage implements LogMessage
{
    /**
     * @var \Exception
     */
    private $exception;

    /**
     * @param \Exception $exception
     */
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
     * @return \Exception
     */
    public function getContext()
    {
        return $this->exception;
    }
}
