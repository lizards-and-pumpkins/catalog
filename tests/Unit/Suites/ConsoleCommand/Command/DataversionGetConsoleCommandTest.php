<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\Argument\Manager as CliMateArgumentManager;
use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\ConsoleCommand;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\Command\DataversionGetConsoleCommand
 * @uses   \LizardsAndPumpkins\ConsoleCommand\BaseCliCommand
 */
class DataversionGetConsoleCommandTest extends TestCase
{
    /**
     * @var MasterFactory
     */
    private $stubMasterFactory;

    /**
     * @var CLImate
     */
    private $mockCliMate;

    final protected function setUp(): void
    {
        $this->stubMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge(get_class_methods(MasterFactory::class), get_class_methods(CommonFactory::class)))
            ->getMock();

        $this->mockCliMate = $this->getMockBuilder(CLImate::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(CLImate::class), ['output']))
            ->getMock();
        $this->mockCliMate->arguments = $this->createMock(CliMateArgumentManager::class);
    }

    public function testIsAConsoleCommand(): void
    {
        $command = new DataversionGetConsoleCommand($this->stubMasterFactory, $this->mockCliMate);
        $this->assertInstanceOf(ConsoleCommand::class, $command);
    }

    public function testOutputsTheCurrentDataVersion(): void
    {
        $stubDataPoolReader = $this->createMock(DataPoolReader::class);
        $stubDataPoolReader->method('getCurrentDataVersion')->willReturn('bar');
        $stubDataPoolReader->method('getPreviousDataVersion')->willReturn('foo');
        $this->stubMasterFactory->method('createDataPoolReader')->willReturn($stubDataPoolReader);
        
        $this->mockCliMate->expects($this->exactly(2))->method('output')->withConsecutive(
            ['Current data version:  bar'],
            ['Previous data version: foo']
        );

        $command = new DataversionGetConsoleCommand($this->stubMasterFactory, $this->mockCliMate);
        $command->run();
    }
}
