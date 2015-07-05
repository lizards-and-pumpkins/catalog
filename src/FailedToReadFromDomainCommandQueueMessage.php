<?php

namespace Brera;

class FailedToReadFromDomainCommandQueueMessage implements LogMessage
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
            "Failed to read from domain command queue message with following exception:\n\n%s",
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
}
