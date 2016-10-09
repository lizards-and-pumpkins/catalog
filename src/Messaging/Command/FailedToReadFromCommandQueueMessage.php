<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Logging\LogMessage;

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

    public function __toString() : string
    {
        return sprintf(
            "Failed to read from command queue message with following exception:\n\n%s",
            $this->exception->getMessage()
        );
    }

    /**
     * @return mixed[]
     */
    public function getContext() : array
    {
        return ['exception' => $this->exception];
    }

    public function getContextSynopsis() : string
    {
        return sprintf('File: %s:%d', $this->exception->getFile(), $this->exception->getLine());
    }
}
