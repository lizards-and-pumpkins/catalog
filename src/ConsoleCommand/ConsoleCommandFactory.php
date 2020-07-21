<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use LizardsAndPumpkins\ConsoleCommand\Command\EventProcessingTimeAverage\LogfileReader;
use LizardsAndPumpkins\ConsoleCommand\Command\EventProcessingTimeAverage\ProcessingTimeTableDataBuilder;
use LizardsAndPumpkins\Core\Factory\Factory;
use LizardsAndPumpkins\Core\Factory\FactoryTrait;

class ConsoleCommandFactory implements Factory
{
    use FactoryTrait;

    public function createConsoleCommandLocator(): ConsoleCommandLocator
    {
        return new NameToClassConvertingConsoleCommandLocator(); 
    }

    public function createProcessingTimeTableDataBuilder(): ProcessingTimeTableDataBuilder
    {
        return new ProcessingTimeTableDataBuilder();
    }

    public function createDomainEventProcessingTimesLogFileReader(): LogfileReader
    {
        return new LogfileReader();
    }
}
