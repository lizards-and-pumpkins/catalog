<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\Argument\Manager as CliMateArgumentManager;
use League\CLImate\CLImate;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\Messaging\Command\CommandConsumer;
use LizardsAndPumpkins\Messaging\Event\DomainEventConsumer;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\Command\ImportTemplateConsoleCommand
 * @uses   \LizardsAndPumpkins\ConsoleCommand\BaseCliCommand
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent
 */
class ImportTemplateConsoleCommandTest extends TestCase
{
    private $testTemplateId = 'foo_page_template';

    private $testDataVersion = 'bar';

    /**
     * @var MasterFactory
     */
    private $stubMasterFactory;

    /**
     * @var CLImate
     */
    private $stubCliMate;

    /**
     * @var CliMateArgumentManager
     */
    private $mockCliArguments;

    private function getCommandArgumentMap($overRideDefaults = []): array
    {
        $arguments = [
            'processQueues' => ['processQueues', false],
            'list'          => ['list', false],
            'templateId'    => ['templateId', $this->testTemplateId],
            'dataVersion'   => ['dataVersion', null],
            'help'          => ['help', null],
        ];
        foreach ($overRideDefaults as $name => $value) {
            $arguments[$name] = [$name, $value];
        }

        return array_values($arguments);
    }

    /**
     * @param MasterFactory|MockObject $stubMasterFactory
     */
    private function runCommand($stubMasterFactory): void
    {
        $command = new ImportTemplateConsoleCommand($stubMasterFactory, $this->stubCliMate);
        $command->run();
    }

    final protected function setUp(): void
    {
        $this->stubMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge([
                'createDataPoolReader',
                'getEventQueue',
                'createTemplateProjectorLocator',
                'createCommandConsumer',
                'createDomainEventConsumer',
            ], get_class_methods(MasterFactory::class)))
            ->getMock();

        $testValidTemplateIds = ['foo', $this->testTemplateId];

        $this->stubMasterFactory->method('createDataPoolReader')->willReturn($this->createMock(DataPoolReader::class));
        $this->stubMasterFactory->createDataPoolReader()->method('getCurrentDataVersion')
            ->willReturn($this->testDataVersion);

        $mockEventQueue = $this->createMock(DomainEventQueue::class);
        $this->stubMasterFactory->method('getEventQueue')->willReturn($mockEventQueue);

        $stubTemplateProjectorLocator = $this->createMock(TemplateProjectorLocator::class);
        $this->stubMasterFactory->method('createTemplateProjectorLocator')->willReturn($stubTemplateProjectorLocator);
        $this->stubMasterFactory->createTemplateProjectorLocator()->method('getRegisteredProjectorCodes')
            ->willReturn($testValidTemplateIds);

        $this->stubCliMate = $this->getMockBuilder(CLImate::class)
            ->setMethods(['output', 'error'])
            ->getMock();
        $this->mockCliArguments = $this->createMock(CliMateArgumentManager::class);
        $this->stubCliMate->arguments = $this->mockCliArguments;
    }

    public function testImportsTheSpecifiedTemplate(): void
    {
        $this->mockCliArguments->method('get')->willReturnMap($this->getCommandArgumentMap());

        $this->stubMasterFactory->getEventQueue()
            ->expects($this->once())->method('add')
            ->with($this->isInstanceOf(TemplateWasUpdatedDomainEvent::class));

        $this->runCommand($this->stubMasterFactory);
    }

    public function testProcessesQueuesIfRequested(): void
    {
        $commandArgumentMap = $this->getCommandArgumentMap(['processQueues' => true]);
        $this->mockCliArguments->method('get')->willReturnMap($commandArgumentMap);

        $mockCommandConsumer = $this->createMock(CommandConsumer::class);
        $mockCommandConsumer->expects($this->once())->method('processAll');
        $this->stubMasterFactory->method('createCommandConsumer')->willReturn($mockCommandConsumer);

        $mockEventConsumer = $this->createMock(DomainEventConsumer::class);
        $mockEventConsumer->expects($this->once())->method('processAll');
        $this->stubMasterFactory->method('createDomainEventConsumer')->willReturn($mockEventConsumer);

        $this->runCommand($this->stubMasterFactory);
    }

    public function testOutputsValidTemplateIdsIfRequested(): void
    {
        $commandArgumentMap = $this->getCommandArgumentMap(['list' => true]);
        $this->mockCliArguments->method('get')->willReturnMap($commandArgumentMap);
        $this->stubCliMate->expects($this->exactly(2))->method('output')->withConsecutive(
            ['Available template IDs:'],
            [$this->stringContains($this->testTemplateId)]
        );
        $this->runCommand($this->stubMasterFactory);
    }

    public function testThrowsExceptionIfAnInvalidTemplateIdIsProvided(): void
    {
        $commandArgumentMap = $this->getCommandArgumentMap(['templateId' => 'invalid foo']);
        $this->mockCliArguments->method('get')->willReturnMap($commandArgumentMap);

        $this->stubCliMate->expects($this->atLeastOnce())->method('error')->willReturnCallback(function () {
            $args = func_get_args();

            if (strpos($args[0], ':') === false) {
                $this->assertStringContainsString('Invalid template ID "invalid foo"', $args[0]);
            }
        });

        $this->runCommand($this->stubMasterFactory);
    }

    public function testUsesCurrentDataVersionIfNotSpecified(): void
    {
        $this->mockCliArguments->method('get')->willReturnMap($this->getCommandArgumentMap());

        $this->stubMasterFactory->getEventQueue()
            ->expects($this->once())->method('add')
            ->with($this->callback(function (TemplateWasUpdatedDomainEvent $event) {
                return $this->testDataVersion === (string) $event->getDataVersion();
            })); 
        $this->runCommand($this->stubMasterFactory);
    }

    public function testUsesSpecifiedDataVersionIfPresentInArguments(): void
    {
        $dataVersion = 'foobar123';
        $this->mockCliArguments->method('get')
            ->willReturnMap($this->getCommandArgumentMap(['dataVersion' => $dataVersion]));

        $this->stubMasterFactory->getEventQueue()
            ->expects($this->once())->method('add')
            ->with($this->callback(function (TemplateWasUpdatedDomainEvent $event) use ($dataVersion) {
                return $dataVersion === (string) $event->getDataVersion();
            })); 
        $this->runCommand($this->stubMasterFactory);
    }
}
