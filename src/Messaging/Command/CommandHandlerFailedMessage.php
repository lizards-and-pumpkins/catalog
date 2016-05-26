<?php

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Messaging\Queue\Message;

class CommandHandlerFailedMessage implements LogMessage
{
    /**
     * @var Message
     */
    private $command;

    /**
     * @var \Exception
     */
    private $exception;

    public function __construct(Message $command, \Exception $exception)
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
            $this->command->getName(),
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
