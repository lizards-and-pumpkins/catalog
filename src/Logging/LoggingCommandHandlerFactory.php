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
     * @param Message $message
     * @return CommandHandler
     */
    public function createUpdateContentBlockCommandHandler(Message $message)
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createUpdateContentBlockCommandHandler($message)
        );
    }

    /**
     * @param Message $message
     * @return CommandHandler
     */
    public function createUpdateProductCommandHandler(Message $message)
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createUpdateProductCommandHandler($message)
        );
    }

    /**
     * @param Message $message
     * @return CommandHandler
     */
    public function createAddProductListingCommandHandler(Message $message)
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createAddProductListingCommandHandler($message)
        );
    }

    /**
     * @param Message $message
     * @return CommandHandler
     */
    public function createAddImageCommandHandler(Message $message)
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createAddImageCommandHandler($message)
        );
    }
}
