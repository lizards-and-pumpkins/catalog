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
    public function testImplementsTheFactoryInterface(): void
    {
        $this->assertInstanceOf(Factory::class, new ConsoleCommandFactory());
    }

    public function testReturnsAConsoleCommandLocator(): void
    {
        $consoleCommandLocator = (new ConsoleCommandFactory())->createConsoleCommandLocator();
        $this->assertInstanceOf(ConsoleCommandLocator::class, $consoleCommandLocator);
    }

    public function testReturnsAProcessingTimeTableDataBuilder(): void
    {
        $processingTimeTableDataBuilder = (new ConsoleCommandFactory())->createProcessingTimeTableDataBuilder();
        $this->assertInstanceOf(ProcessingTimeTableDataBuilder::class, $processingTimeTableDataBuilder);
    }

    public function testReturnsADomainEventProcesingTimesLogFileReader(): void
    {
        $logFileReader = (new ConsoleCommandFactory())->createDomainEventProcessingTimesLogFileReader();
        $this->assertInstanceOf(LogfileReader::class, $logFileReader);
    }
}
