<?php

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;

use LizardsAndPumpkins\Messaging\Command\CommandProcessedLogMessage;

class ProcessTimeLoggingCommandHandlerDecorator implements CommandHandler
{
    /**
     * @var CommandHandler
     */
    private $decoratedCommandHandler;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(CommandHandler $decoratedCommandHandler, Logger $logger)
    {
        $this->decoratedCommandHandler = $decoratedCommandHandler;
        $this->logger = $logger;
    }
    
    public function process()
    {
        $startTime = microtime(true);
        $this->decoratedCommandHandler->process();
        $processTime = microtime(true) - $startTime;
        $msg = sprintf('CommandHandler::process %s %f', get_class($this->decoratedCommandHandler), $processTime);
        $this->logger->log(new CommandProcessedLogMessage($msg, $this));
    }
}
