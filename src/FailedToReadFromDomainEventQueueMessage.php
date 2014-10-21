<?php

namespace Brera\PoC;

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
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }
} 
