<?php

namespace LizardsAndPumpkins\ProductDetail\Import;

use LizardsAndPumpkins\Import\Product\ProductDTO;
use LizardsAndPumpkins\Import\Product\UpdateProductCommand;

/**
 * @covers \LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory
 * @uses   \LizardsAndPumpkins\Import\Product\UpdateProductCommand
 */
class UpdatingProductImportCommandFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdatingProductImportCommandFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new UpdatingProductImportCommandFactory();
    }

    public function testItImplementsTheProductImportCommandFactoryInterface()
    {
        $this->assertInstanceOf(ProductImportCommandFactory::class, $this->factory);
    }

    public function testItReturnsAnUpdateProductCommand()
    {
        /** @var ProductDTO|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->createMock(ProductDTO::class);
        $stubProduct->method('jsonSerialize')->willReturn([]);
        $stubProduct->method('getId')->willReturn('dummy');
        $commands = $this->factory->createProductImportCommands($stubProduct);
        
        $this->assertInternalType('array', $commands);
        $this->assertContainsOnlyInstancesOf(UpdateProductCommand::class, $commands);
    }
}
