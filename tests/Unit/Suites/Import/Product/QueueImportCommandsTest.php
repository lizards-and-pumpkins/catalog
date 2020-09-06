<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use LizardsAndPumpkins\Import\Product\Image\ProductImageImportCommandLocator;
use LizardsAndPumpkins\Import\Product\Listing\ProductListingImportCommandLocator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\QueueImportCommands
 */
class QueueImportCommandsTest extends TestCase
{
    /**
     * @var QueueImportCommands
     */
    private $createImportCommands;

    /**
     * @var Command|MockObject
     */
    private $stubCommand;

    /**
     * @var CommandQueue|MockObject
     */
    private $mockCommandQueue;

    /**
     * @var ProductImportCommandLocator|MockObject
     */
    private $mockProductCommandLocator;

    /**
     * @var ProductImageImportCommandLocator|MockObject
     */
    private $mockImageCommandLocator;

    /**
     * @var ProductListingImportCommandLocator|MockObject
     */
    private $mockListingCommandLocator;

    final protected function setUp(): void
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

    public function testItAddsCreatedProductCommandsToTheQueue(): void
    {
        $this->mockProductCommandLocator->method('getProductImportCommands')->willReturn([$this->stubCommand]);
        $this->mockCommandQueue->expects($this->once())->method('add');
        $this->createImportCommands->forProduct($this->createMock(Product::class));
    }

    public function testItAddsCreatedProductImageCommandsToTheQueue(): void
    {
        $this->mockImageCommandLocator
            ->method('getProductImageImportCommands')
            ->willReturn([$this->stubCommand]);
        $this->mockCommandQueue->expects($this->once())->method('add');
        $this->createImportCommands->forImage('foo.jpg', $this->createMock(DataVersion::class));
    }

    public function testItAddsCreatedProductListingCommandsToTheQueue(): void
    {
        $this->mockListingCommandLocator
            ->method('getProductListingImportCommands')
            ->willReturn([$this->stubCommand]);
        $this->mockCommandQueue->expects($this->once())->method('add');
        $this->createImportCommands->forListing($this->createMock(ProductListing::class));
    }
}
