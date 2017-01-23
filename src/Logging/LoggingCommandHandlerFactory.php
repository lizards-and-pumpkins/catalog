<?php

declare(strict_types=1);

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

    private function getCommandFactoryDelegate() : CommandHandlerFactory
    {
        return $this->commandFactoryDelegate;
    }

    public function createUpdateContentBlockCommandHandler(Message $message) : CommandHandler
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createUpdateContentBlockCommandHandler($message)
        );
    }

    public function createUpdateProductCommandHandler(Message $message) : CommandHandler
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createUpdateProductCommandHandler($message)
        );
    }

    public function createAddProductListingCommandHandler(Message $message) : CommandHandler
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createAddProductListingCommandHandler($message)
        );
    }

    public function createAddImageCommandHandler(Message $message) : CommandHandler
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createAddImageCommandHandler($message)
        );
    }

    public function createShutdownWorkerCommandHandler(Message $message) : CommandHandler
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createShutdownWorkerCommandHandler($message)
        );
    }
    
    public function createImportCatalogCommandHandler(Message $message): CommandHandler
    {
        $commandFactoryDelegate = $this->getCommandFactoryDelegate();
        return $commandFactoryDelegate->createProcessTimeLoggingCommandHandlerDecorator(
            $commandFactoryDelegate->createImportCatalogCommandHandler($message)
        );
    }
}
