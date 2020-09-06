<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail\Import;

use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\UpdateProductCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory
 * @uses   \LizardsAndPumpkins\Import\Product\UpdateProductCommand
 */
class UpdatingProductImportCommandFactoryTest extends TestCase
{
    /**
     * @var UpdatingProductImportCommandFactory
     */
    private $factory;

    final protected function setUp(): void
    {
        $this->factory = new UpdatingProductImportCommandFactory();
    }

    public function testItImplementsTheProductImportCommandFactoryInterface(): void
    {
        $this->assertInstanceOf(ProductImportCommandFactory::class, $this->factory);
    }

    public function testItReturnsAnUpdateProductCommand(): void
    {
        /** @var Product|MockObject $stubProduct */
        $stubProduct = $this->createMock(Product::class);
        $stubProduct->method('jsonSerialize')->willReturn([]);
        $stubProduct->method('getId')->willReturn('dummy');
        $commands = $this->factory->createProductImportCommands($stubProduct);
        
        $this->assertIsArray($commands);
        $this->assertContainsOnlyInstancesOf(UpdateProductCommand::class, $commands);
    }
}
