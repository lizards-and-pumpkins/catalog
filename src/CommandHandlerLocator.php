<?php

namespace Brera;

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
        $commandClass = $this->getUnqualifiedCommandClassName($command);
        $method = 'create' . $commandClass . 'Handler';

        if (!method_exists($this->factory, $method)) {
            throw new UnableToFindCommandHandlerException(
                sprintf('Unable to find a handler for %s command', $commandClass)
            );
        }

        return $this->factory->{$method}($command);
    }

    /**
     * @param Command $command
     * @return string
     */
    private function getUnqualifiedCommandClassName(Command $command)
    {
        $qualifiedClassName = get_class($command);
        $lastQualifierPosition = strrpos($qualifiedClassName, '\\');

        if (false === $lastQualifierPosition) {
            return $qualifiedClassName;
        }

        return substr($qualifiedClassName, $lastQualifierPosition + 1);
    }
}
