<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Logging;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Command\CommandHandlerFactory;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;

class LoggingCommandHandlerFactory implements CommandHandlerFactory, Factory
{
    use FactoryTrait;

    /**
     * @var CommandHandlerFactory
     */
    private $commandFactoryDelegate;

    public function __construct(CommandHandlerFactory $commandFactoryDelegate)
    {
        $this->commandFactoryDelegate = $commandFactoryDelegate;
    }
    
    private function getCommandFactoryDelegate(): CommandHandlerFactory
    {
        return $this->commandFactoryDelegate;
    }

    public function createUpdateContentBlockCommandHandler(Message $command): CommandHandler
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createUpdateContentBlockCommandHandler($command)
        );
    }

    public function createUpdateProductCommandHandler(Message $command): CommandHandler
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createUpdateProductCommandHandler($command)
        );
    }

    public function createAddProductListingCommandHandler(Message $command): CommandHandler
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createAddProductListingCommandHandler($command)
        );
    }

    public function createAddImageCommandHandler(Message $command): CommandHandler
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createAddImageCommandHandler($command)
        );
    }
}
