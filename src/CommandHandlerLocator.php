<?php

namespace Brera;

use Brera\Product\ProjectProductStockQuantitySnippetCommand;

class CommandHandlerLocator
{
    /**
     * @var CommandFactory
     */
    private $factory;

    public function __construct(CommandFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param Command $command
     * @return CommandHandler
     * @throws UnableToFindCommandHandlerException
     */
    public function getHandlerFor(Command $command)
    {
        $commandClass = get_class($command);

        switch ($commandClass) {
            case ProjectProductStockQuantitySnippetCommand::class:
                return $this->factory->createProjectProductStockQuantitySnippetCommandHandler($command);
        }

        throw new UnableToFindCommandHandlerException(
            sprintf('Unable to find a handler for %s domain command', $commandClass)
        );
    }
}
