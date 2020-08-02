<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use LizardsAndPumpkins\ConsoleCommand\Exception\InvalidConsoleCommandNameException;

class NameToClassConvertingConsoleCommandLocator implements ConsoleCommandLocator
{
    public function hasClassForName(string $commandName): bool
    {
        $this->validateCommandName($commandName);
        return class_exists($this->buildClassName($commandName));
    }

    public function getClassFromName(string $commandName): string
    {
        if (! $this->hasClassForName($commandName)) {
            throw new InvalidConsoleCommandNameException(sprintf('The command "%s" is unknown', $commandName));
        }
        return $this->buildClassName($commandName);
    }

    private function buildClassName(string $commandName): string
    {
        $class = str_replace(' ', '', ucwords(str_replace([':', '-'], ' ', trim($commandName)))) . 'ConsoleCommand';

        return '\\LizardsAndPumpkins\\ConsoleCommand\\Command\\' . $class;
    }

    private function validateCommandName(string $commandName): void
    {
        if ('' === trim($commandName)) {
            throw new InvalidConsoleCommandNameException('The command name must not be an empty string');
        }
        if (preg_match('#[^a-z:-]#i', $commandName)) {
            throw  new InvalidConsoleCommandNameException(sprintf('The command name "%s" is invalid', $commandName));
        }
    }
}
