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
use LizardsAndPumpkins\Core\Factory\MasterFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
     * @var CliMateArgumentManager|MockObject
     */
    private $mockCliArguments;

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

    /**
     * @param MasterFactory|MockObject $mockMasterFactory
     */
    private function runCommand($mockMasterFactory): void
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

    final protected function setUp(): void
    {
        $this->mockMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->onlyMethods(get_class_methods(MasterFactory::class))
            ->addMethods(array_merge(['get'], get_class_methods(CommonFactory::class)))
            ->getMock();

        $this->mockCatalogImport = $this->createMock(CatalogImport::class);

        $this->mockCliArguments = $this->createMock(CliMateArgumentManager::class);

        $this->stubCliMate = $this->createMock(CLImate::class);
        $this->stubCliMate->arguments = $this->mockCliArguments;
    }

    public function testAlwaysRegistersTheImportFactories(): void
    {
        $this->mockCliArguments->method('get')->willReturnMap($this->getCommandArgumentMap());

        $this->mockMasterFactory->expects($this->at(0))->method('register')
            ->with($this->isInstanceOf(UpdatingProductImportCommandFactory::class));
        $this->mockMasterFactory->expects($this->at(1))->method('register')
            ->with($this->isInstanceOf(UpdatingProductListingImportCommandFactory::class));

        $this->runCommand($this->mockMasterFactory);
    }

    public function testDoesNotClearStorageIfRequested(): void
    {
        $this->mockCliArguments->method('get')->willReturnMap($this->getCommandArgumentMap());

        $mockDataPoolWriter = $this->mockDataPoolWriter($this->mockMasterFactory);
        $mockDataPoolWriter->expects($this->never())->method('clear');

        $this->runCommand($this->mockMasterFactory);
    }

    public function testClearsStorageIfRequested(): void
    {
        $arguments = $this->getCommandArgumentMap(['clearStorage' => true]);

        $this->mockCliArguments->method('get')->willReturnMap($arguments);
        $mockDataPoolWriter = $this->mockDataPoolWriter($this->mockMasterFactory);
        $mockDataPoolWriter->expects($this->once())->method('clear');

        $this->runCommand($this->mockMasterFactory);
    }

    public function testDoesNotEnableImageImportIfNotRequested(): void
    {
        $this->mockCliArguments->method('get')->willReturnMap($this->getCommandArgumentMap());

        $this->mockMasterFactory->expects($this->any())->method('register')->willReturnCallback(function () {
            $args = func_get_args();
            $this->assertTrue(in_array(get_class($args[0]), [
                UpdatingProductImportCommandFactory::class,
                UpdatingProductListingImportCommandFactory::class,
                NullProductImageImportCommandFactory::class,
            ]));
            $this->assertNotSame(UpdatingProductImageImportCommandFactory::class, get_class($args[0]));
        });

        $this->runCommand($this->mockMasterFactory);
    }

    public function testEnablesImageImportIfRequested(): void
    {
        $arguments = $this->getCommandArgumentMap(['importImages' => true]);
        $this->mockCliArguments->method('get')->willReturnMap($arguments);

        $this->mockMasterFactory->expects($this->any())->method('register')->willReturnCallback(function () {
            $args = func_get_args();
            $this->assertTrue(in_array(get_class($args[0]), [
                UpdatingProductImportCommandFactory::class,
                UpdatingProductListingImportCommandFactory::class,
                UpdatingProductImageImportCommandFactory::class,
            ]));
            $this->assertNotSame(NullProductImageImportCommandFactory::class, get_class($args[0]));
        });

        $this->runCommand($this->mockMasterFactory);
    }

    public function testImportsSpecifiedFile(): void
    {
        $this->mockCliArguments->method('get')->willReturnMap($this->getCommandArgumentMap());

        $this->mockCatalogImport->expects($this->once())->method('importFile')
            ->with($this->dummyImportFile, $this->isInstanceOf(DataVersion::class));
        $this->runCommand($this->mockMasterFactory);
    }

    public function testDoesNotProcessesQueuesIfNotRequested(): void
    {
        $this->mockCliArguments->method('get')->willReturnMap($this->getCommandArgumentMap());

        $mockCommandConsumer = $this->createMock(CommandConsumer::class);
        $mockCommandConsumer->expects($this->never())->method('processAll');
        $this->mockMasterFactory->method('createCommandConsumer')->willReturn($mockCommandConsumer);

        $mockEventConsumer = $this->createMock(DomainEventConsumer::class);
        $mockEventConsumer->expects($this->never())->method('processAll');
        $this->mockMasterFactory->method('createDomainEventConsumer')->willReturn($mockEventConsumer);

        $this->runCommand($this->mockMasterFactory);
    }

    public function testProcessesQueuesIfRequested(): void
    {
        $arguments = $this->getCommandArgumentMap(['processQueues' => true]);
        $this->mockCliArguments->method('get')->willReturnMap($arguments);

        $mockCommandConsumer = $this->createMock(CommandConsumer::class);
        $mockCommandConsumer->expects($this->once())->method('processAll');
        $this->mockMasterFactory->method('createCommandConsumer')->willReturn($mockCommandConsumer);

        $mockEventConsumer = $this->createMock(DomainEventConsumer::class);
        $mockEventConsumer->expects($this->once())->method('processAll');
        $this->mockMasterFactory->method('createDomainEventConsumer')->willReturn($mockEventConsumer);

        $this->runCommand($this->mockMasterFactory);
    }

    public function testUsesTheCurrentDataVersionIfNoneIsSpecified(): void
    {
        $this->mockCliArguments->method('get')->willReturnMap($this->getCommandArgumentMap());
        $this->mockCatalogImport->expects($this->once())->method('importFile')
            ->with($this->anything(), $this->equalTo($this->testDataVersion));
        $this->runCommand($this->mockMasterFactory);
    }

    public function testUsesTheSpecifiedDataVersion(): void
    {
        $this->mockCliArguments->method('get')
            ->willReturnMap($this->getCommandArgumentMap(['dataVersion' => 'bar']));
        $this->mockCatalogImport->expects($this->once())->method('importFile')
            ->with($this->anything(), $this->equalTo('bar'));
        $this->runCommand($this->mockMasterFactory);
    }
}
