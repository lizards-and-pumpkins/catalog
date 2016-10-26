<?php

declare(strict_types=1);

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

    public function __toString() : string
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
    public function getContext() : array
    {
        return ['exception' => $this->exception];
    }

    public function getContextSynopsis() : string
    {
        return sprintf('File: %s:%d', $this->exception->getFile(), $this->exception->getLine());
    }
}
