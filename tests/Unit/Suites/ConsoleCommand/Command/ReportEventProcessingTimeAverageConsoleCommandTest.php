<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\Argument\Manager as CliMateArgumentManager;
use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\Command\EventProcessingTimeAverage\LogfileReader;
use LizardsAndPumpkins\ConsoleCommand\Command\EventProcessingTimeAverage\ProcessingTimeTableDataBuilder;
use LizardsAndPumpkins\ConsoleCommand\ConsoleCommandFactory;
use LizardsAndPumpkins\TestFileFixtureTrait;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\Command\ReportEventProcessingTimeAverageConsoleCommand
 * @covers \LizardsAndPumpkins\ConsoleCommand\Command\EventProcessingTimeAverage\ProcessingTimeTableDataBuilder
 * @uses   \LizardsAndPumpkins\ConsoleCommand\BaseCliCommand
 */
class ReportEventProcessingTimeAverageConsoleCommandTest extends TestCase
{
    use TestFileFixtureTrait;
    
    /**
     * @var CLImate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCliMate;

    /**
     * @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubMasterFactory;

    private function getCommandArgumentMap($overRideDefaults = []): array
    {
        $arguments = [
            'sortBy'    => ['sortBy', 'avg'],
            'direction' => ['direction', 'asc'],
            'logfile'   => ['logfile', __FILE__],
            'help'      => ['help', null],
        ];
        foreach ($overRideDefaults as $name => $value) {
            $arguments[$name] = [$name, $value];
        }

        return array_values($arguments);
    }

    protected function setUp()
    {
        $this->stubMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(
                get_class_methods(MasterFactory::class),
                get_class_methods(ConsoleCommandFactory::class)
            ))
            ->getMock();

        $this->stubMasterFactory->method('createProcessingTimeTableDataBuilder')
            ->willReturn(new ProcessingTimeTableDataBuilder());

        $mockLogFileReader = $this->createMock(LogfileReader::class);
        $this->stubMasterFactory->method('createDomainEventProcessingTimesLogFileReader')
            ->willReturn($mockLogFileReader);

        $this->mockCliMate = $this->getMockBuilder(CLImate::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(CLImate::class), ['table', 'yellow', 'error']))
            ->getMock();
        $this->mockCliMate->arguments = $this->createMock(CliMateArgumentManager::class);
    }

    public function testOutputsTableDataFromLogfile()
    {
        $this->stubMasterFactory->createDomainEventProcessingTimesLogFileReader()
            ->method('getEventHandlerProcessingTimes')->willReturn([
                'Foo' => [1, 2, 3],
                'Bar' => [1.5, 2.5, 3.5],
            ]);
        
        $this->mockCliMate->arguments->method('get')->willReturnMap($this->getCommandArgumentMap());

        $this->mockCliMate->expects($this->never())->method('error');
        $this->mockCliMate->expects($this->once())->method('table')->with($this->countOf(2));
        (new ReportEventProcessingTimeAverageConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();
    }

    public function testsShowsMessageIfNoRecordsInLogfile()
    {
        $this->stubMasterFactory->createDomainEventProcessingTimesLogFileReader()
            ->method('getEventHandlerProcessingTimes')->willReturn([]);

        $this->mockCliMate->arguments->method('get')->willReturnMap($this->getCommandArgumentMap());

        $this->mockCliMate->expects($this->never())->method('error');
        $this->mockCliMate->expects($this->never())->method('table');
        $this->mockCliMate->expects($this->once())->method('yellow')->with('No data to report');
        (new ReportEventProcessingTimeAverageConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();
    }

    public function testValidatesLogFileIsReadable()
    {
        $filePath = $this->getUniqueTempDir() . 'test-log.log';
        $this->createFixtureFile($filePath, '', 0000);

        $argumentsMap = $this->getCommandArgumentMap(['logfile' => $filePath]);
        $this->mockCliMate->arguments->method('get')->willReturnMap($argumentsMap);
        
        $this->mockCliMate->expects($this->atLeastOnce())->method('error')
            ->withConsecutive([$this->stringStartsWith('Log file not readable')]);
        (new ReportEventProcessingTimeAverageConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();
    }

    public function testValidatesLogFileExists()
    {
        $argumentsMap = $this->getCommandArgumentMap(['logfile' => '/foo/does/not/exist.txt']);
        $this->mockCliMate->arguments->method('get')->willReturnMap($argumentsMap);
        
        $this->mockCliMate->expects($this->atLeastOnce())->method('error')
            ->withConsecutive([$this->stringStartsWith('Log file not found')]);
        (new ReportEventProcessingTimeAverageConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();
    }

    public function testValidatesSortField()
    {
        $argumentsMap = $this->getCommandArgumentMap(['sortBy' => 'foo']);
        $this->mockCliMate->arguments->method('get')->willReturnMap($argumentsMap);
        
        $this->mockCliMate->expects($this->atLeastOnce())->method('error')
            ->withConsecutive([$this->stringStartsWith('Invalid sort field')]);
        (new ReportEventProcessingTimeAverageConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();
    }

    public function testValidatesSortDirection()
    {
        $argumentsMap = $this->getCommandArgumentMap(['direction' => 'bar']);
        $this->mockCliMate->arguments->method('get')->willReturnMap($argumentsMap);
        
        $this->mockCliMate->expects($this->atLeastOnce())->method('error')
            ->withConsecutive([$this->stringStartsWith('Invalid sort direction')]);
        (new ReportEventProcessingTimeAverageConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();
    }
}
