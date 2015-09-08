<?php

namespace Brera;

use Brera\Log\LogMessage;

class FailedToReadFromCommandQueueMessage implements LogMessage
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
            "Failed to read from command queue message with following exception:\n\n%s",
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
