<?php

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Logging\LogMessage;

class CommandHandlerFailedMessage implements LogMessage
{
    /**
     * @var Command
     */
    private $command;

    /**
     * @var \Exception
     */
    private $exception;

    public function __construct(Command $command, \Exception $exception)
    {
        $this->command = $command;
        $this->exception = $exception;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            "Failure during processing %s command with following message:\n\n%s",
            get_class($this->command),
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
