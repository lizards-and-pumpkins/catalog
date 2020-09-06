<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\Argument\Manager as CliMateArgumentManager;
use League\CLImate\CLImate;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand;
use LizardsAndPumpkins\Messaging\Command\CommandConsumer;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Messaging\Event\DomainEventConsumer;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
use LizardsAndPumpkins\Util\FileSystem\TestFileFixtureTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\Command\ImportContentBlockConsoleCommand
 * @uses   \LizardsAndPumpkins\ConsoleCommand\BaseCliCommand
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand
 */
class ImportContentBlockConsoleCommandTest extends TestCase
{
    private $testDataVersion = 'baz';

    use TestFileFixtureTrait;

    /**
     * @var MasterFactory
     */
    private $stubMasterFactory;

    /**
     * @var CLImate
     */
    private $mockCliMate;

    /**
     * @var string
     */
    private $testImportDirectory;

    /**
     * @var ContextSource
     */
    private $stubContextSource;

    /**
     * @var CliMateArgumentManager
     */
    private $mockCliArguments;

    private function getCommandArgumentMap($overRideDefaults = []): array
    {
        $arguments = [
            'importDirectory' => ['importDirectory', $this->testImportDirectory],
            'processQueues'   => ['processQueues', false],
            'dataVersion'     => ['dataVersion', null],
            'help'            => ['help', null],
        ];
        foreach ($overRideDefaults as $name => $value) {
            $arguments[$name] = [$name, $value];
        }

        return array_values($arguments);
    }

    final protected function setUp(): void
    {
        $methods = array_merge(
            get_class_methods(MasterFactory::class),
            get_class_methods(CommonFactory::class),
            ['createContextSource']
        );

        $this->stubContextSource = $this->createMock(ContextSource::class);

        $this->stubMasterFactory = $this->getMockBuilder(MasterFactory::class)->setMethods($methods)->getMock();
        $this->stubMasterFactory->method('createContextSource')->willReturn($this->stubContextSource);

        $stubDataPoolReader = $this->createMock(DataPoolReader::class);
        $stubDataPoolReader->method('getCurrentDataVersion')->willReturn($this->testDataVersion);
        $this->stubMasterFactory->method('createDataPoolReader')->willReturn($stubDataPoolReader);

        $this->mockCliMate = $this->getMockBuilder(CLImate::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(CLImate::class), ['error', 'yellow']))
            ->getMock();

        $this->mockCliArguments = $this->createMock(CliMateArgumentManager::class);
        $this->mockCliMate->arguments = $this->mockCliArguments;

        $this->testImportDirectory = $this->getUniqueTempDir();
    }

    public function testImportsNonProductListingContentBlocks(): void
    {
        $this->stubContextSource->method('getAllAvailableContextsWithVersionApplied')
            ->willReturn([$this->createMock(Context::class)]);

        $this->mockCliArguments->method('get')->willReturnMap($this->getCommandArgumentMap());
        $this->mockCliMate->expects($this->never())->method('error');

        $this->createFixtureFile($this->testImportDirectory . '/content_block_foo.html', 'dummy content');

        $mockCommandQueue = $this->createMock(CommandQueue::class);
        $mockCommandQueue->expects($this->once())->method('add')
            ->with($this->isInstanceOf(UpdateContentBlockCommand::class))
            ->willReturnCallback(function (UpdateContentBlockCommand $updateContentBlockCommand) {
                $contentBlockSource = $updateContentBlockCommand->getContentBlockSource();
                $this->assertEquals('content_block_foo', $contentBlockSource->getContentBlockId());
            });

        $this->stubMasterFactory->method('getCommandQueue')->willReturn($mockCommandQueue);

        (new ImportContentBlockConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();
    }

    public function testImportsProductListingContentBlocks(): void
    {
        $this->stubContextSource->method('getAllAvailableContextsWithVersionApplied')
            ->willReturn([$this->createMock(Context::class)]);

        $this->mockCliArguments->method('get')->willReturnMap($this->getCommandArgumentMap());
        $this->mockCliMate->expects($this->never())->method('error');

        $this->createFixtureFile(
            $this->testImportDirectory . '/product_listing_content_block_foobar.html',
            'dummy content'
        );

        $mockCommandQueue = $this->createMock(CommandQueue::class);
        $mockCommandQueue->expects($this->once())->method('add')
            ->with($this->isInstanceOf(UpdateContentBlockCommand::class))
            ->willReturnCallback(function (UpdateContentBlockCommand $updateContentBlockCommand) {
                $contentBlockSource = $updateContentBlockCommand->getContentBlockSource();
                $this->assertEquals('product_listing_content_block_', $contentBlockSource->getContentBlockId());
                $this->assertEquals('foobar', $contentBlockSource->getKeyGeneratorParams()['url_key']);
            });

        $this->stubMasterFactory->method('getCommandQueue')->willReturn($mockCommandQueue);

        (new ImportContentBlockConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();
    }

    public function testProcessesQueuesIfRequested(): void
    {
        $arguments = $this->getCommandArgumentMap(['processQueues' => true]);
        $this->mockCliArguments->method('get')->willReturnMap($arguments);

        $mockCommandConsumer = $this->createMock(CommandConsumer::class);
        $mockCommandConsumer->expects($this->once())->method('processAll');
        $this->stubMasterFactory->method('createCommandConsumer')->willReturn($mockCommandConsumer);

        $mockEventConsumer = $this->createMock(DomainEventConsumer::class);
        $mockEventConsumer->expects($this->once())->method('processAll');
        $this->stubMasterFactory->method('createDomainEventConsumer')->willReturn($mockEventConsumer);

        $this->mockCliMate->expects($this->never())->method('error');

        (new ImportContentBlockConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();
    }

    public function testDisplaysWarningsIfContentBlockIdsDoNotStartWithAValidPrefix(): void
    {
        $this->createFixtureFile($this->testImportDirectory . '/foobar.html', 'dummy content');
        $this->stubMasterFactory->method('getCommandQueue')->willReturn($this->createMock(CommandQueue::class));

        $this->mockCliMate->expects($this->atLeastOnce())->method('yellow');
        $this->mockCliMate->expects($this->never())->method('error');

        $this->mockCliArguments->method('get')->willReturnMap($this->getCommandArgumentMap());

        (new ImportContentBlockConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();
    }

    public function testUsesTheCurrentDataVersionIfNoneIsSpecified(): void
    {
        $this->mockCliArguments->method('get')->willReturnMap($this->getCommandArgumentMap());
        $this->createFixtureFile($this->testImportDirectory . '/content_block_foo.html', 'dummy content');
        $this->stubMasterFactory->method('getCommandQueue')->willReturn($this->createMock(CommandQueue::class));

        $this->stubContextSource->expects($this->any())->method('getAllAvailableContextsWithVersionApplied')
            ->willReturnCallback(function () {
                $args = func_get_args();
                $this->assertSame($this->testDataVersion, $args[0]);

                return [$this->createMock(Context::class)];
            });

        (new ImportContentBlockConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();
    }

    public function testUsesTheSpecifiedDataVersion(): void
    {
        $dataVersion = 'foobar123';
        
        $this->mockCliArguments->method('get')
            ->willReturnMap($this->getCommandArgumentMap(['dataVersion' => $dataVersion]));
        $this->createFixtureFile($this->testImportDirectory . '/content_block_foo.html', 'dummy content');
        $this->stubMasterFactory->method('getCommandQueue')->willReturn($this->createMock(CommandQueue::class));

        $this->stubContextSource->expects($this->any())->method('getAllAvailableContextsWithVersionApplied')
            ->willReturnCallback(function () use ($dataVersion) {
                $args = func_get_args();
                $this->assertSame($dataVersion, $args[0]);

                return [$this->createMock(Context::class)];
            });

        (new ImportContentBlockConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();
    }
}
