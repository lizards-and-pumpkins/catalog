<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\Argument\Manager as CliMateArgumentManager;
use League\CLImate\CLImate;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\Command\ReportUrlKeysConsoleCommand
 * @uses   \LizardsAndPumpkins\ConsoleCommand\BaseCliCommand
 */
class ReportUrlKeysConsoleCommandTest extends TestCase
{
    /**
     * @var MasterFactory
     */
    private $stubMasterFactory;

    /**
     * @var CLImate
     */
    private $mockCliMate;

    private $testUrlKeyRecords = [
        ['foo.html', '', 'product'],
        ['bar.html', '', 'product'],
        ['baz.html', '', 'product'],
        ['qux.html', '', 'listing'],
        ['caz.html', '', 'listing'],
    ];

    private function getCommandArgumentMap($overRideDefaults = []): array
    {
        $arguments = [
            'type'        => ['type', ReportUrlKeysConsoleCommand::TYPE_ALL],
            'dataVersion' => ['dataVersion', ReportUrlKeysConsoleCommand::CURRENT_VERSION],
            'help'        => ['help', null],
        ];
        foreach ($overRideDefaults as $name => $value) {
            $arguments[$name] = [$name, $value];
        }

        return array_values($arguments);
    }

    final protected function setUp(): void
    {
        $stubDataPoolReader = $this->createMock(DataPoolReader::class);
        $stubDataPoolReader->method('getCurrentDataVersion')->willReturn('xxx');
        $stubDataPoolReader->method('getUrlKeysForVersion')->willReturn($this->testUrlKeyRecords);
        $this->stubMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge(get_class_methods(MasterFactory::class), ['createDataPoolReader']))
            ->getMock();

        $this->stubMasterFactory->method('createDataPoolReader')->willReturn($stubDataPoolReader);

        $this->mockCliMate = $this->getMockBuilder(CLImate::class)
            ->setMethods(['output', 'error', 'bold'])
            ->getMock();
        $this->mockCliMate->arguments = $this->createMock(CliMateArgumentManager::class);
    }

    public function testOutputsAllUrlKeysByDefault(): void
    {
        $argumentsMap = $this->getCommandArgumentMap(['type' => ReportUrlKeysConsoleCommand::TYPE_ALL]);
        $this->mockCliMate->arguments->method('get')->willReturnMap($argumentsMap);
        
        $this->mockCliMate->expects($this->exactly(count($this->testUrlKeyRecords)))->method('output');
        (new ReportUrlKeysConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();
    }

    public function testOutputsProductUrlKeysIfSpecified(): void
    {
        $argumentsMap = $this->getCommandArgumentMap(['type' => ReportUrlKeysConsoleCommand::TYPE_PRODUCT]);
        $this->mockCliMate->arguments->method('get')->willReturnMap($argumentsMap);
        
        $numberOfProductUrlKeys = 3;
        $this->mockCliMate->expects($this->exactly($numberOfProductUrlKeys))->method('output');
        (new ReportUrlKeysConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();
    }

    public function testOutputsListingUrlKeysIfSpecified(): void
    {
        $argumentsMap = $this->getCommandArgumentMap(['type' => ReportUrlKeysConsoleCommand::TYPE_LISTING]);
        $this->mockCliMate->arguments->method('get')->willReturnMap($argumentsMap);
        
        $numberOfListingUrlKeys = 2;
        $this->mockCliMate->expects($this->exactly($numberOfListingUrlKeys))->method('output');
        (new ReportUrlKeysConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();
    }
}
