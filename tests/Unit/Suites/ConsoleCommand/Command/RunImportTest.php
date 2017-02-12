<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\Argument\Manager as CliMateArgumentManager;
use League\CLImate\CLImate;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Import\CatalogImport;
use LizardsAndPumpkins\Import\Image\NullProductImageImportCommandFactory;
use LizardsAndPumpkins\Import\Image\UpdatingProductImageImportCommandFactory;
use LizardsAndPumpkins\Messaging\Command\CommandConsumer;
use LizardsAndPumpkins\Messaging\Event\DomainEventConsumer;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\Command\RunImport
 * @uses \LizardsAndPumpkins\ConsoleCommand\BaseCliCommand
 * @uses \LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class RunImportTest extends TestCase
{
    private $dummyImportFile = '/foo/bar.xml';

    /**
     * @var MasterFactory|MockObject
     */
    private $mockMasterFactory;

    /**
     * @var CLImate|MockObject
     */
    private $mockCliMate;

    /**
     * @var CatalogImport|MockObject
     */
    private $mockCatalogImport;

    private function getCommandArgumentMap($overRideDefaults = []): array
    {
        $arguments = [
            'clearStorage'      => ['clearStorage', false],
            'importImages'      => ['importImages', false],
            'importFile'        => ['importFile', $this->dummyImportFile],
            'processQueues'     => ['processQueues', false],
            'environmentConfig' => ['environmentConfig', ''],
            'help'              => ['help', null],
        ];
        foreach ($overRideDefaults as $name => $value) {
            $arguments[$name] = [$name, $value];
        }
        return array_values($arguments);
    }

    private function runCommand(MockObject $mockMasterFactory)
    {
        $this->mockDataPoolReader($mockMasterFactory);
        $mockMasterFactory->method('createCatalogImport')->willReturn($this->mockCatalogImport);
        $command = new RunImport($this->mockMasterFactory, $this->mockCliMate);
        $command->run();
    }

    private function mockDataPoolWriter(MockObject $mockMasterFactory): MockObject
    {
        $mockDataPoolWriter = $this->createMock(DataPoolWriter::class);
        $mockMasterFactory->method('createDataPoolWriter')->willReturn($mockDataPoolWriter);

        return $mockDataPoolWriter;
    }

    private function mockDataPoolReader(MockObject $mockMasterFactory): MockObject
    {
        $stubDataPoolReader = $this->createMock(DataPoolReader::class);
        $stubDataPoolReader->method('getCurrentDataVersion')->willReturn('foo');
        $mockMasterFactory->method('createDataPoolReader')->willReturn($stubDataPoolReader);

        return $stubDataPoolReader;
    }

    protected function setUp()
    {
        $this->mockMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge(['register'], get_class_methods(CommonFactory::class)))
            ->getMock();
        $this->mockCatalogImport = $this->createMock(CatalogImport::class);
        $this->mockCliMate = $this->createMock(CLImate::class);
        $this->mockCliMate->arguments = $this->createMock(CliMateArgumentManager::class);
    }

    public function testDoesNotClearStorageIfRequested()
    {
        $this->mockCliMate->arguments->method('get')->willReturnMap($this->getCommandArgumentMap());
        
        $mockDataPoolWriter = $this->mockDataPoolWriter($this->mockMasterFactory);
        $mockDataPoolWriter->expects($this->never())->method('clear');

        $this->runCommand($this->mockMasterFactory);
    }

    public function testClearsStorageIfRequested()
    {
        $arguments = $this->getCommandArgumentMap(['clearStorage' => true]);
        
        $this->mockCliMate->arguments->method('get')->willReturnMap($arguments);
        $mockDataPoolWriter = $this->mockDataPoolWriter($this->mockMasterFactory);
        $mockDataPoolWriter->expects($this->once())->method('clear');

        $this->runCommand($this->mockMasterFactory);
    }

    public function testDoesNotEnableImageImportIfNotRequested()
    {
        $this->mockCliMate->arguments->method('get')->willReturnMap($this->getCommandArgumentMap());
        $this->mockMasterFactory->expects($this->once())->method('register')
            ->with($this->isInstanceOf(NullProductImageImportCommandFactory::class));

        $this->runCommand($this->mockMasterFactory);
    }

    public function testEnablesImageImportIfRequested()
    {
        $arguments = $this->getCommandArgumentMap(['importImages' => true]);
        $this->mockCliMate->arguments->method('get')->willReturnMap($arguments);
        $this->mockMasterFactory->expects($this->once())->method('register')
            ->with($this->isInstanceOf(UpdatingProductImageImportCommandFactory::class));

        $this->runCommand($this->mockMasterFactory);
    }

    public function testImportsSpecifiedFile()
    {
        $this->mockCliMate->arguments->method('get')->willReturnMap($this->getCommandArgumentMap());

        $this->mockCatalogImport->expects($this->once())->method('importFile')
            ->with($this->dummyImportFile, $this->isInstanceOf(DataVersion::class));
        $this->runCommand($this->mockMasterFactory);
    }

    public function testDoesNotProcessesQueuesIfNotRequested()
    {
        $this->mockCliMate->arguments->method('get')->willReturnMap($this->getCommandArgumentMap());

        $mockCommandConsumer = $this->createMock(CommandConsumer::class);
        $mockCommandConsumer->expects($this->never())->method('processAll');
        $this->mockMasterFactory->method('createCommandConsumer')->willReturn($mockCommandConsumer);
        
        $mockEventConsumer = $this->createMock(DomainEventConsumer::class);
        $mockEventConsumer->expects($this->never())->method('processAll');
        $this->mockMasterFactory->method('createDomainEventConsumer')->willReturn($mockEventConsumer);
        
        $this->runCommand($this->mockMasterFactory);
    }

    public function testProcessesQueuesIfRequested()
    {
        $arguments = $this->getCommandArgumentMap(['processQueues' => true]);
        $this->mockCliMate->arguments->method('get')->willReturnMap($arguments);

        $mockCommandConsumer = $this->createMock(CommandConsumer::class);
        $mockCommandConsumer->expects($this->once())->method('processAll');
        $this->mockMasterFactory->method('createCommandConsumer')->willReturn($mockCommandConsumer);

        $mockEventConsumer = $this->createMock(DomainEventConsumer::class);
        $mockEventConsumer->expects($this->once())->method('processAll');
        $this->mockMasterFactory->method('createDomainEventConsumer')->willReturn($mockEventConsumer);

        $this->runCommand($this->mockMasterFactory);
    }
}
