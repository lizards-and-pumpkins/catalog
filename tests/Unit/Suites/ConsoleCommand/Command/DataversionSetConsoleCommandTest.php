<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\Argument\Manager as CliMateArgumentManager;
use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\ConsoleCommand;
use LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommand;
use LizardsAndPumpkins\Messaging\Command\CommandConsumer;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Messaging\Event\DomainEventConsumer;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\Command\DataversionSetConsoleCommand
 * @uses   \LizardsAndPumpkins\ConsoleCommand\BaseCliCommand
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommand
 */
class DataversionSetConsoleCommandTest extends TestCase
{
    /**
     * @var CLImate|MockObject
     */
    private $mockCliMate;

    private function getCommandArgumentMap($overRideDefaults = []): array
    {
        $arguments = [
            'dataVersion'   => ['dataVersion', 'bar'],
            'processQueues' => ['processQueues', false],
            'help'          => ['help', null],
        ];
        foreach ($overRideDefaults as $name => $value) {
            $arguments[$name] = [$name, $value];
        }

        return array_values($arguments);
    }

    private function createCLIMateTestDouble(): MockObject
    {
        $mockCliMate = $this->getMockBuilder(CLImate::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(CLImate::class), ['error', 'output']))
            ->getMock();
        $mockCliMate->arguments = $this->createMock(CliMateArgumentManager::class);

        return $mockCliMate;
    }

    /**
     * @return MasterFactory|MockObject
     */
    private function createStubMasterFactory(): MasterFactory
    {
        $methods = array_merge(
            get_class_methods(MasterFactory::class),
            get_class_methods(CommonFactory::class)
        );
        return $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge($methods))
            ->getMock();
    }

    final protected function setUp(): void
    {
        $this->mockCliMate = $this->createCLIMateTestDouble();
    }

    public function testIsAConsoleCommand(): void
    {
        $consoleCommand = new DataversionSetConsoleCommand($this->createStubMasterFactory(), $this->mockCliMate);
        $this->assertInstanceOf(ConsoleCommand::class, $consoleCommand);
    }

    public function testAddsSetDataVersionCommandWithTheSpecifiedDataVersion(): void
    {
        $arguments = $this->getCommandArgumentMap(['dataVersion' => 'foo']);
        $this->mockCliMate->arguments->method('get')->willReturnMap($arguments);
        
        $mockCommandQueue = $this->createMock(CommandQueue::class);
        $mockCommandQueue->expects($this->once())->method('add')
            ->with($this->callback(function (SetCurrentDataVersionCommand $command) {
                return (string) $command->getDataVersion() === 'foo';
            }));
        
        $stubMasterFactory = $this->createStubMasterFactory();
        $stubMasterFactory->method('getCommandQueue')->willReturn($mockCommandQueue);

        $command = new DataversionSetConsoleCommand($stubMasterFactory, $this->mockCliMate);
        $command->run();
    }

    public function testProcessesQueuesIfRequested(): void
    {
        $arguments = $this->getCommandArgumentMap(['processQueues' => true]);
        $this->mockCliMate->arguments->method('get')->willReturnMap($arguments);
        $stubMasterFactory = $this->createStubMasterFactory();
        $stubMasterFactory->method('getCommandQueue')->willReturn($this->createMock(CommandQueue::class));
        
        $mockCommandConsumer = $this->createMock(CommandConsumer::class);
        $stubMasterFactory->method('createCommandConsumer')->willReturn($mockCommandConsumer);
        $mockEventConsumer = $this->createMock(DomainEventConsumer::class);
        $stubMasterFactory->method('createDomainEventConsumer')->willReturn($mockEventConsumer);

        $mockCommandConsumer->expects($this->once())->method('processAll');
        $mockEventConsumer->expects($this->once())->method('processAll');
        $this->mockCliMate->expects($this->never())->method('error');

        $command = new DataversionSetConsoleCommand($stubMasterFactory, $this->mockCliMate);
        $command->run();
    }
}
