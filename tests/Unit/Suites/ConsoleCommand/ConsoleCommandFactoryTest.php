<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use LizardsAndPumpkins\ConsoleCommand\Command\EventProcessingTimeAverage\LogfileReader;
use LizardsAndPumpkins\ConsoleCommand\Command\EventProcessingTimeAverage\ProcessingTimeTableDataBuilder;
use LizardsAndPumpkins\Core\Factory\Factory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\ConsoleCommandFactory
 * @uses   \LizardsAndPumpkins\ConsoleCommand\Command\EventProcessingTimeAverage\LogfileReader
 */
class ConsoleCommandFactoryTest extends TestCase
{
    public function testImplementsTheFactoryInterface()
    {
        $this->assertInstanceOf(Factory::class, new ConsoleCommandFactory());
    }

    public function testReturnsAConsoleCommandLocator()
    {
        $consoleCommandLocator = (new ConsoleCommandFactory())->createConsoleCommandLocator();
        $this->assertInstanceOf(ConsoleCommandLocator::class, $consoleCommandLocator);
    }

    public function testReturnsAProcessingTimeTableDataBuilder()
    {
        $processingTimeTableDataBuilder = (new ConsoleCommandFactory())->createProcessingTimeTableDataBuilder();
        $this->assertInstanceOf(ProcessingTimeTableDataBuilder::class, $processingTimeTableDataBuilder);
    }

    public function testReturnsADomainEventProcesingTimesLogFileReader()
    {
        $logFileReader = (new ConsoleCommandFactory())->createDomainEventProcessingTimesLogFileReader();
        $this->assertInstanceOf(LogfileReader::class, $logFileReader);
    }
}
