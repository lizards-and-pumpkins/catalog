<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\UpdateProductCommand;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\UpdatingProductImportCommandFactory
 * @uses   \LizardsAndPumpkins\Product\UpdateProductCommand
 */
class UpdatingProductImportCommandFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdatingProductImportCommandFactory
     */
    private $factory;

    /**
     * @param string $className
     * @param mixed[] $array
     */
    private function assertContainsInstanceOf($className, array $array)
    {
        $found = array_reduce($array, function ($found, $value) use ($className) {
            return $found || $value instanceof $className;
        }, false);
        $this->assertTrue($found, sprintf('Failed asserting that the array contains an instance of "%s"', $className));
    }

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
        $commands = $this->factory->createProductImportCommands($stubProduct);
        $this->assertInternalType('array', $commands);
        $this->assertContainsOnlyInstancesOf(Command::class, $commands);
        $this->assertContainsInstanceOf(UpdateProductCommand::class, $commands);
    }
}
