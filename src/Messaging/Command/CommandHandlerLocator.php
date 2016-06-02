<?php

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

    /**
     * @param Message $command
     * @return CommandHandler
     */
    public function getHandlerFor(Message $command)
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

    /**
     * @param Message $event
     * @return string
     */
    private function getUnqualifiedCommandClassName(Message $event)
    {
        $camelCaseEventName = $this->snakeCaseToCamelCase($event->getName() . '_command');
        return $camelCaseEventName . 'Handler';
    }

    /**
     * @param string $name
     * @return string
     */
    private function snakeCaseToCamelCase($name)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }
}
