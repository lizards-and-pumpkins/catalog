<?php

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

    /**
     * @return CommandHandlerFactory
     */
    private function getCommandFactoryDelegate()
    {
        return $this->commandFactoryDelegate;
    }

    /**
     * @param Message $command
     * @return CommandHandler
     */
    public function createUpdateContentBlockCommandHandler(Message $command)
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createUpdateContentBlockCommandHandler($command)
        );
    }

    /**
     * @param Message $command
     * @return CommandHandler
     */
    public function createUpdateProductCommandHandler(Message $command)
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createUpdateProductCommandHandler($command)
        );
    }

    /**
     * @param Message $command
     * @return CommandHandler
     */
    public function createAddProductListingCommandHandler(Message $command)
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createAddProductListingCommandHandler($command)
        );
    }

    /**
     * @param Message $command
     * @return CommandHandler
     */
    public function createAddImageCommandHandler(Message $command)
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createAddImageCommandHandler($command)
        );
    }
}
