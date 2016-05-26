<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Messaging\Command;

use LizardsAndPumpkins\Messaging\Command\Exception\UnableToFindCommandHandlerException;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class CommandHandlerLocator
{
    /**
     * @var CommandHandlerFactory
     */
    private $factory;

    public function __construct(MasterFactory $factory)
    {
        $this->factory = $factory;
    }

    public function getHandlerFor(Message $command): CommandHandler
    {
        $commandHandlerClass = $this->getUnqualifiedCommandClassName($command);
        $method = 'create' . $commandHandlerClass;

        if (!method_exists(CommandHandlerFactory::class, $method)) {
            throw new UnableToFindCommandHandlerException(
                sprintf('Unable to find a handler "%s" for command "%s"', $commandHandlerClass, $command->getName())
            );
        }

        return $this->factory->{$method}($command);
    }

    private function getUnqualifiedCommandClassName(Message $event): string
    {
        $camelCaseEventName = $this->snakeCaseToCamelCase($event->getName());
        return $camelCaseEventName . 'Handler';
    }

    private function snakeCaseToCamelCase(string $name): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }
}
