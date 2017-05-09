<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\Argument\Manager as CliMateArgumentManager;
use League\CLImate\CLImate;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\Messaging\Command\CommandConsumer;
use LizardsAndPumpkins\Messaging\Event\DomainEventConsumer;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\Command\ImportTemplateConsoleCommand
 * @uses   \LizardsAndPumpkins\ConsoleCommand\BaseCliCommand
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent
 */
class ImportTemplateConsoleCommandTest extends TestCase
{
    private $testTemplateId = 'foo_page_template';

    private $testValidTemplateIds;

    /**
     * @var MasterFactory|MockObject
     */
    private $stubMasterFactory;

    /**
     * @var CLImate|MockObject
     */
    private $stubCliMate;

    private function getCommandArgumentMap($overRideDefaults = []): array
    {
        $arguments = [
            'processQueues' => ['processQueues', false],
            'list'          => ['list', false],
            'templateId'    => ['templateId', $this->testTemplateId],
            'help'          => ['help', null],
        ];
        foreach ($overRideDefaults as $name => $value) {
            $arguments[$name] = [$name, $value];
        }

        return array_values($arguments);
    }

    private function runCommand(MockObject $stubMasterFactory)
    {
        $command = new ImportTemplateConsoleCommand($stubMasterFactory, $this->stubCliMate);
        $command->run();
    }

    protected function setUp()
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

        $this->testValidTemplateIds = ['foo', $this->testTemplateId];
        
        $this->stubMasterFactory->method('createDataPoolReader')->willReturn($this->createMock(DataPoolReader::class));
        $this->stubMasterFactory->createDataPoolReader()->method('getCurrentDataVersion')->willReturn('bar');

        $mockEventQueue = $this->createMock(DomainEventQueue::class);
        $this->stubMasterFactory->method('getEventQueue')->willReturn($mockEventQueue);

        $stubTemplateProjectorLocator = $this->createMock(TemplateProjectorLocator::class);
        $this->stubMasterFactory->method('createTemplateProjectorLocator')->willReturn($stubTemplateProjectorLocator);
        $this->stubMasterFactory->createTemplateProjectorLocator()->method('getRegisteredProjectorCodes')
            ->willReturn($this->testValidTemplateIds);

        $this->stubCliMate = $this->getMockBuilder(CLImate::class)
            ->setMethods(['output', 'error'])
            ->getMock();
        $this->stubCliMate->arguments = $this->createMock(CliMateArgumentManager::class);
    }

    public function testImportsTheSpecifiedTemplate()
    {
        $this->stubCliMate->arguments->method('get')->willReturnMap($this->getCommandArgumentMap());
        
        $this->stubMasterFactory->getEventQueue()
            ->expects($this->once())->method('add')
            ->with($this->isInstanceOf(TemplateWasUpdatedDomainEvent::class));
        
        $this->runCommand($this->stubMasterFactory);
    }

    public function testProcessesQueuesIfRequested()
    {
        $commandArgumentMap = $this->getCommandArgumentMap(['processQueues' => true]);
        $this->stubCliMate->arguments->method('get')->willReturnMap($commandArgumentMap);
        
        $mockCommandConsumer = $this->createMock(CommandConsumer::class);
        $mockCommandConsumer->expects($this->once())->method('processAll');
        $this->stubMasterFactory->method('createCommandConsumer')->willReturn($mockCommandConsumer);
        
        $mockEventConsumer = $this->createMock(DomainEventConsumer::class);
        $mockEventConsumer->expects($this->once())->method('processAll');
        $this->stubMasterFactory->method('createDomainEventConsumer')->willReturn($mockEventConsumer);

        $this->runCommand($this->stubMasterFactory);
    }

    public function testOutputsValidTemplateIdsIfRequested()
    {
        $commandArgumentMap = $this->getCommandArgumentMap(['list' => true]);
        $this->stubCliMate->arguments->method('get')->willReturnMap($commandArgumentMap);
        $this->stubCliMate->expects($this->exactly(2))->method('output')->withConsecutive(
            ['Available template IDs:'],
            [$this->stringContains($this->testTemplateId)]
        );
        $this->runCommand($this->stubMasterFactory);
    }

    public function testThrowsExceptionIfAnInvalidTemplateIdIsProvided()
    {
        $commandArgumentMap = $this->getCommandArgumentMap(['templateId' => 'invalid foo']);
        $this->stubCliMate->arguments->method('get')->willReturnMap($commandArgumentMap);
        $errorOutputSpy = $this->atLeastOnce();
        $this->stubCliMate->expects($errorOutputSpy)->method('error');
        $this->runCommand($this->stubMasterFactory);
        
        $expectationFulfilled = array_reduce(
            $errorOutputSpy->getInvocations(),
            function ($found, \PHPUnit_Framework_MockObject_Invocation $invocation) {
                return $found || strpos($invocation->parameters[0], 'Invalid template ID "invalid foo"') === false;
            }
        );
        $this->assertTrue($expectationFulfilled, "Expected message not output as error.");
    }
}
