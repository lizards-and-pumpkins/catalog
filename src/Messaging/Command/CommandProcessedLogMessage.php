<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Logging\LogMessage;

class CommandProcessedLogMessage implements LogMessage
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var CommandHandler
     */
    private $commandHandler;

    public function __construct(string $message, CommandHandler $commandHandler)
    {
        $this->message = $message;
        $this->commandHandler = $commandHandler;
    }

    public function __toString() : string
    {
        return $this->message;
    }

    /**
     * @return mixed[]
     */
    public function getContext() : array
    {
        return ['command_handler' => $this->commandHandler];
    }

    public function getContextSynopsis() : string
    {
        return sprintf('CommandHandler Class: %s', get_class($this->commandHandler));
    }
}
