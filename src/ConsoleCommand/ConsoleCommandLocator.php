<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

interface ConsoleCommandLocator
{
    public function hasClassForName(string $commandName): bool;
    
    public function getClassFromName(string $commandName): string;
}
