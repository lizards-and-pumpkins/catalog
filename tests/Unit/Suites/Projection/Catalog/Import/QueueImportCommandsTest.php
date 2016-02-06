<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductListing;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\ProductImageImportCommandLocator;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\ProductImportCommandLocator;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\ProductListingImportCommandLocator;
use LizardsAndPumpkins\Queue\Queue;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\QueueImportCommands
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
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
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
        $this->mockCommandQueue = $this->getMock(Queue::class);
        $this->stubCommand = $this->getMock(Command::class);
        $this->mockProductCommandLocator = $this->getMock(ProductImportCommandLocator::class, [], [], '', false);
        $this->mockImageCommandLocator = $this->getMock(ProductImageImportCommandLocator::class, [], [], '', false);
        $this->mockListingCommandLocator = $this->getMock(ProductListingImportCommandLocator::class, [], [], '', false);
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
        $this->mockCommandQueue->expects($this->once())->method('add')->with($this->stubCommand);
        $this->createImportCommands->forProduct($this->getMock(Product::class));
    }

    public function testItAddsCreatedProductImageCommandsToTheQueue()
    {
        $this->mockImageCommandLocator
            ->method('getProductImageImportCommands')
            ->willReturn([$this->stubCommand]);
        $this->mockCommandQueue->expects($this->once())->method('add')->with($this->stubCommand);
        $this->createImportCommands->forImage('foo.jpg', $this->getMock(DataVersion::class, [], [], '', false));
    }

    public function testItAddsCreatedProductListingCommandsToTheQueue()
    {
        $this->mockListingCommandLocator
            ->method('getProductListingImportCommands')
            ->willReturn([$this->stubCommand]);
        $this->mockCommandQueue->expects($this->once())->method('add')->with($this->stubCommand);
        $this->createImportCommands->forListing($this->getMock(ProductListing::class, [], [], '', false));
    }
}

