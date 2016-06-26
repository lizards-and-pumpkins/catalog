<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use LizardsAndPumpkins\Import\Product\Image\ProductImageImportCommandLocator;
use LizardsAndPumpkins\Import\Product\Listing\ProductListingImportCommandLocator;
use LizardsAndPumpkins\Messaging\Queue;

/**
 * @covers \LizardsAndPumpkins\Import\Product\QueueImportCommands
 */
class QueueImportCommandsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QueueImportCommands
     */
    private $createImportCommands;

    /**
     * @var Command|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubCommand;

    /**
     * @var CommandQueue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommandQueue;

    /**
     * @var ProductImportCommandLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductCommandLocator;

    /**
     * @var ProductImageImportCommandLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockImageCommandLocator;

    /**
     * @var ProductListingImportCommandLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockListingCommandLocator;

    protected function setUp()
    {
        $this->mockCommandQueue = $this->createMock(CommandQueue::class);
        $this->stubCommand = $this->createMock(Command::class);
        $this->mockProductCommandLocator = $this->createMock(ProductImportCommandLocator::class);
        $this->mockImageCommandLocator = $this->createMock(ProductImageImportCommandLocator::class);
        $this->mockListingCommandLocator = $this->createMock(ProductListingImportCommandLocator::class);
        $this->createImportCommands = new QueueImportCommands(
            $this->mockCommandQueue,
            $this->mockProductCommandLocator,
            $this->mockImageCommandLocator,
            $this->mockListingCommandLocator
        );
    }

    public function testItAddsCreatedProductCommandsToTheQueue()
    {
        $this->mockProductCommandLocator->method('getProductImportCommands')->willReturn([$this->stubCommand]);
        $this->mockCommandQueue->expects($this->once())->method('add');
        $this->createImportCommands->forProduct($this->createMock(ProductDTO::class));
    }

    public function testItAddsCreatedProductImageCommandsToTheQueue()
    {
        $this->mockImageCommandLocator
            ->method('getProductImageImportCommands')
            ->willReturn([$this->stubCommand]);
        $this->mockCommandQueue->expects($this->once())->method('add');
        $this->createImportCommands->forImage('foo.jpg', $this->createMock(DataVersion::class));
    }

    public function testItAddsCreatedProductListingCommandsToTheQueue()
    {
        $this->mockListingCommandLocator
            ->method('getProductListingImportCommands')
            ->willReturn([$this->stubCommand]);
        $this->mockCommandQueue->expects($this->once())->method('add');
        $this->createImportCommands->forListing($this->createMock(ProductListing::class));
    }
}
