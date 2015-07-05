<?php

namespace Brera;

use Brera\Product\ProjectProductStockQuantitySnippetDomainCommand;

class DomainCommandHandlerLocator
{
    /**
     * @var DomainCommandFactory
     */
    private $factory;

    public function __construct(DomainCommandFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param DomainCommand $command
     * @return mixed
     * @throws UnableToFindDomainCommandHandlerException
     */
    public function getHandlerFor(DomainCommand $command)
    {
        $commandClass = get_class($command);

        switch ($commandClass) {
            case ProjectProductStockQuantitySnippetDomainCommand::class:
                return $this->factory->createProjectProductStockQuantitySnippetDomainCommandHandler($command);
        }

        throw new UnableToFindDomainCommandHandlerException(
            sprintf('Unable to find a handler for %s domain command', $commandClass)
        );
    }
}
