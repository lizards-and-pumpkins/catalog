<?php

namespace LizardsAndPumpkins\ProductDetail\Import;

use LizardsAndPumpkins\Import\Product\Product;

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
        $stubProduct = $this->getMock(Product::class);
        $stubProduct->method('jsonSerialize')->willReturn([]);
        $stubProduct->method('getId')->willReturn('dummy');
        $commands = $this->factory->createProductImportCommands($stubProduct);
        
        $this->assertInternalType('array', $commands);
        
        $expectedPayload = json_encode(['id' => 'dummy', 'product' => $stubProduct]);
        array_map(function (array $commandData) use ($expectedPayload) {
            if (! isset($commandData['name']) || 'update_product' !== $commandData['name']) {
                $this->fail('"name" array record must contain the command name "update_product"');
            }

            if (! isset($commandData['payload']) || $commandData['payload'] !== $expectedPayload) {
                $this->fail('"payload" array record must contain payload "' . compact($expectedPayload) . '"');
            }
        }, $commands);
    }
}
