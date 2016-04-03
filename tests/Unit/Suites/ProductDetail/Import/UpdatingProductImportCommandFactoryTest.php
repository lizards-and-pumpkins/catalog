<?php

namespace LizardsAndPumpkins\ProductDetail\Import;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Import\Product\Product;
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
