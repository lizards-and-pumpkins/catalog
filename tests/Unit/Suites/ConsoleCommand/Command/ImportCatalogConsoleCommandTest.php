<?php

declare(strict_types=1);

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
use LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\Command\ImportCatalogConsoleCommand
 * @uses   \LizardsAndPumpkins\ConsoleCommand\BaseCliCommand
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class ImportCatalogConsoleCommandTest extends TestCase
{
    private $dummyImportFile = '/foo/bar.xml';

    private $testDataVersion = 'foo';

    /**
     * @var MasterFactory|MockObject
     */
    private $mockMasterFactory;

    /**
     * @var CLImate|MockObject
     */
    private $stubCliMate;

    /**
     * @var CatalogImport|MockObject
     */
    private $mockCatalogImport;

    /**
     * @var \PHPUnit_Framework_MockObject_Matcher_InvokedRecorder
     */
    private $registerFactorySpy;

    private function getCommandArgumentMap($overRideDefaults = []): array
    {
        $arguments = [
            'clearStorage'  => ['clearStorage', false],
            'importImages'  => ['importImages', false],
            'importFile'    => ['importFile', $this->dummyImportFile],
            'processQueues' => ['processQueues', false],
            'dataVersion'   => ['dataVersion', null],
            'help'          => ['help', null],
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
        $command = new ImportCatalogConsoleCommand($mockMasterFactory, $this->stubCliMate);
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
        $stubDataPoolReader->method('getCurrentDataVersion')->willReturn($this->testDataVersion);
        $mockMasterFactory->method('createDataPoolReader')->willReturn($stubDataPoolReader);

        return $stubDataPoolReader;
    }

    private function getRegisteredFactoryClassNames()
    {
        return array_map(function (\PHPUnit_Framework_MockObject_Invocation_Static $invocation) {
            return get_class($invocation->parameters[0]);
        }, $this->registerFactorySpy->getInvocations());
    }

    private function assertFactoryRegistered(string $factoryClassName)
    {
        $message = sprintf('Factory "%s" was not registered with the master factory', $factoryClassName);
        $this->assertContains($factoryClassName, $this->getRegisteredFactoryClassNames(), $message);
    }

    private function assertFactoryNotRegistered(string $factoryClassName)
    {
        $message = sprintf('Factory "%s" was not expected to be registered with the master factory', $factoryClassName);
        $this->assertNotContains($factoryClassName, $this->getRegisteredFactoryClassNames(), $message);
    }

    protected function setUp()
    {
        $this->mockMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge(get_class_methods(MasterFactory::class), get_class_methods(CommonFactory::class)))
            ->getMock();
        $this->registerFactorySpy = $this->any();
        $this->mockMasterFactory->expects($this->registerFactorySpy)->method('register');

        $this->mockCatalogImport = $this->createMock(CatalogImport::class);
        $this->stubCliMate = $this->createMock(CLImate::class);
        $this->stubCliMate->arguments = $this->createMock(CliMateArgumentManager::class);
    }

    public function testAlwaysRegistersTheImportFactories()
    {
        $this->stubCliMate->arguments->method('get')->willReturnMap($this->getCommandArgumentMap());
        $this->runCommand($this->mockMasterFactory);

        $this->assertFactoryRegistered(UpdatingProductImportCommandFactory::class);
        $this->assertFactoryRegistered(UpdatingProductListingImportCommandFactory::class);
    }

    public function testDoesNotClearStorageIfRequested()
    {
        $this->stubCliMate->arguments->method('get')->willReturnMap($this->getCommandArgumentMap());

        $mockDataPoolWriter = $this->mockDataPoolWriter($this->mockMasterFactory);
        $mockDataPoolWriter->expects($this->never())->method('clear');

        $this->runCommand($this->mockMasterFactory);
    }

    public function testClearsStorageIfRequested()
    {
        $arguments = $this->getCommandArgumentMap(['clearStorage' => true]);

        $this->stubCliMate->arguments->method('get')->willReturnMap($arguments);
        $mockDataPoolWriter = $this->mockDataPoolWriter($this->mockMasterFactory);
        $mockDataPoolWriter->expects($this->once())->method('clear');

        $this->runCommand($this->mockMasterFactory);
    }

    public function testDoesNotEnableImageImportIfNotRequested()
    {
        $this->stubCliMate->arguments->method('get')->willReturnMap($this->getCommandArgumentMap());
        $this->runCommand($this->mockMasterFactory);

        $this->assertFactoryRegistered(NullProductImageImportCommandFactory::class);
        $this->assertFactoryNotRegistered(UpdatingProductImageImportCommandFactory::class);
    }

    public function testEnablesImageImportIfRequested()
    {
        $arguments = $this->getCommandArgumentMap(['importImages' => true]);
        $this->stubCliMate->arguments->method('get')->willReturnMap($arguments);
        $this->runCommand($this->mockMasterFactory);

        $this->assertFactoryRegistered(UpdatingProductImageImportCommandFactory::class);
        $this->assertFactoryNotRegistered(NullProductImageImportCommandFactory::class);
    }

    public function testImportsSpecifiedFile()
    {
        $this->stubCliMate->arguments->method('get')->willReturnMap($this->getCommandArgumentMap());

        $this->mockCatalogImport->expects($this->once())->method('importFile')
            ->with($this->dummyImportFile, $this->isInstanceOf(DataVersion::class));
        $this->runCommand($this->mockMasterFactory);
    }

    public function testDoesNotProcessesQueuesIfNotRequested()
    {
        $this->stubCliMate->arguments->method('get')->willReturnMap($this->getCommandArgumentMap());

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
        $this->stubCliMate->arguments->method('get')->willReturnMap($arguments);

        $mockCommandConsumer = $this->createMock(CommandConsumer::class);
        $mockCommandConsumer->expects($this->once())->method('processAll');
        $this->mockMasterFactory->method('createCommandConsumer')->willReturn($mockCommandConsumer);

        $mockEventConsumer = $this->createMock(DomainEventConsumer::class);
        $mockEventConsumer->expects($this->once())->method('processAll');
        $this->mockMasterFactory->method('createDomainEventConsumer')->willReturn($mockEventConsumer);

        $this->runCommand($this->mockMasterFactory);
    }

    public function testUsesTheCurrentDataVersionIfNoneIsSpecified()
    {
        $this->stubCliMate->arguments->method('get')->willReturnMap($this->getCommandArgumentMap());
        $this->mockCatalogImport->expects($this->once())->method('importFile')
            ->with($this->anything(), $this->equalTo($this->testDataVersion));
        $this->runCommand($this->mockMasterFactory);
    }

    public function testUsesTheSpecifiedDataVersion()
    {
        $this->stubCliMate->arguments->method('get')
            ->willReturnMap($this->getCommandArgumentMap(['dataVersion' => 'bar']));
        $this->mockCatalogImport->expects($this->once())->method('importFile')
            ->with($this->anything(), $this->equalTo('bar'));
        $this->runCommand($this->mockMasterFactory);
    }
}
