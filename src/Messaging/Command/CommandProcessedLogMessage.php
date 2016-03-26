<?php

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
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

    /**
     * @param string $message
     * @param CommandHandler $commandHandler
     */
    public function __construct($message, CommandHandler $commandHandler)
    {
        $this->message = $message;
        $this->commandHandler = $commandHandler;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->message;
    }

    /**
     * @return mixed[]
     */
    public function getContext()
    {
        return ['command_handler' => $this->commandHandler];
    }

    /**
     * @return string
     */
    public function getContextSynopsis()
    {
        return sprintf('CommandHandler Class: %s', get_class($this->commandHandler));
    }
}
