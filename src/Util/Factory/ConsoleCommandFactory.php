<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Util\Factory;

use LizardsAndPumpkins\ConsoleCommand\ConsoleCommandLocator;
use LizardsAndPumpkins\ConsoleCommand\NameToClassConvertingConsoleCommandLocator;

class ConsoleCommandFactory implements Factory
{
    use FactoryTrait;

    public function createConsoleCommandLocator(): ConsoleCommandLocator
    {
        return new NameToClassConvertingConsoleCommandLocator(); 
    }
}
