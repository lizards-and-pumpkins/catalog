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
use LizardsAndPumpkins\TestFileFixtureTrait;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
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
     * @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubMasterFactory;

    /**
     * @var CLImate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCliMate;

    /**
     * @var string
     */
    private $testImportDirectory;

    /**
     * @var \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     */
    private $contextSourceSpy;

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

    private function assertDataVersionForImportEquals(string $expectedDataVersionString)
    {
        $invocations = $this->contextSourceSpy->getInvocations();
        if (count($invocations) === 0) {
            $this->fail(sprintf('Method getAllAvailableContextsWithVersionApplied was not called on context source'));
        }
        $dataVersion = $invocations[0]->parameters[0];
        $this->assertEquals($expectedDataVersionString, $dataVersion);
    }

    protected function setUp()
    {
        $methods = array_merge(
            get_class_methods(MasterFactory::class),
            get_class_methods(CommonFactory::class),
            ['createContextSource']
        );
        $this->stubMasterFactory = $this->getMockBuilder(MasterFactory::class)->setMethods($methods)->getMock();

        $stubContextSource = $this->createMock(ContextSource::class);
        $this->contextSourceSpy = $this->any();
        $stubContextSource->expects($this->contextSourceSpy)->method('getAllAvailableContextsWithVersionApplied')
            ->willReturn([$this->createMock(Context::class)]);
        $this->stubMasterFactory->method('createContextSource')->willReturn($stubContextSource);

        $stubDataPoolReader = $this->createMock(DataPoolReader::class);
        $stubDataPoolReader->method('getCurrentDataVersion')->willReturn($this->testDataVersion);
        $this->stubMasterFactory->method('createDataPoolReader')->willReturn($stubDataPoolReader);

        $this->mockCliMate = $this->getMockBuilder(CLImate::class)
            ->disableOriginalConstructor()
            ->setMethods(array_merge(get_class_methods(CLImate::class), ['error', 'yellow']))
            ->getMock();
        $this->mockCliMate->arguments = $this->createMock(CliMateArgumentManager::class);

        $this->testImportDirectory = $this->getUniqueTempDir();
    }

    public function testImportsNonProductListingContentBlocks()
    {
        $this->mockCliMate->arguments->method('get')->willReturnMap($this->getCommandArgumentMap());
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

        $command = new ImportContentBlockConsoleCommand($this->stubMasterFactory, $this->mockCliMate);
        $command->run();
    }

    public function testImportsProductListingContentBlocks()
    {
        $this->mockCliMate->arguments->method('get')->willReturnMap($this->getCommandArgumentMap());
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

        $command = new ImportContentBlockConsoleCommand($this->stubMasterFactory, $this->mockCliMate);
        $command->run();
    }

    public function testProcessesQueuesIfRequested()
    {
        $arguments = $this->getCommandArgumentMap(['processQueues' => true]);
        $this->mockCliMate->arguments->method('get')->willReturnMap($arguments);

        $mockCommandConsumer = $this->createMock(CommandConsumer::class);
        $mockCommandConsumer->expects($this->once())->method('processAll');
        $this->stubMasterFactory->method('createCommandConsumer')->willReturn($mockCommandConsumer);

        $mockEventConsumer = $this->createMock(DomainEventConsumer::class);
        $mockEventConsumer->expects($this->once())->method('processAll');
        $this->stubMasterFactory->method('createDomainEventConsumer')->willReturn($mockEventConsumer);

        $this->mockCliMate->expects($this->never())->method('error');

        $command = new ImportContentBlockConsoleCommand($this->stubMasterFactory, $this->mockCliMate);
        $command->run();
    }

    public function testDisplaysWarningsIfContentBlockIdsDoNotStartWithAValidPrefix()
    {
        $this->createFixtureFile($this->testImportDirectory . '/foobar.html', 'dummy content');
        $this->stubMasterFactory->method('getCommandQueue')->willReturn($this->createMock(CommandQueue::class));

        $this->mockCliMate->expects($this->atLeastOnce())->method('yellow');
        $this->mockCliMate->expects($this->never())->method('error');

        $this->mockCliMate->arguments->method('get')->willReturnMap($this->getCommandArgumentMap());

        $command = new ImportContentBlockConsoleCommand($this->stubMasterFactory, $this->mockCliMate);
        $command->run();
    }

    public function testUsesTheCurrentDataVersionIfNoneIsSpecified()
    {
        $this->mockCliMate->arguments->method('get')->willReturnMap($this->getCommandArgumentMap());
        $this->createFixtureFile($this->testImportDirectory . '/content_block_foo.html', 'dummy content');
        $this->stubMasterFactory->method('getCommandQueue')->willReturn($this->createMock(CommandQueue::class));

        (new ImportContentBlockConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();

        $this->assertDataVersionForImportEquals($this->testDataVersion);
    }

    public function testUsesTheSpecifiedDataVersion()
    {
        $dataVersion = 'foobar123';
        
        $this->mockCliMate->arguments->method('get')
            ->willReturnMap($this->getCommandArgumentMap(['dataVersion' => $dataVersion]));
        $this->createFixtureFile($this->testImportDirectory . '/content_block_foo.html', 'dummy content');
        $this->stubMasterFactory->method('getCommandQueue')->willReturn($this->createMock(CommandQueue::class));

        (new ImportContentBlockConsoleCommand($this->stubMasterFactory, $this->mockCliMate))->run();

        $this->assertDataVersionForImportEquals($dataVersion);
    }
}
