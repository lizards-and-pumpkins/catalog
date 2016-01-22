<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\Product\AddProductListingCommand;
use LizardsAndPumpkins\Product\ProductListingCriteria;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\UpdatingProductListingImportCommandFactory
 * @uses   \LizardsAndPumpkins\Product\AddProductListingCommand
 */
class UpdatingProductListingImportCommandFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdatingProductListingImportCommandFactory
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
        $this->factory = new UpdatingProductListingImportCommandFactory();
    }

    public function testItImplementsTheProductListingImportCommandFactoryInterface()
    {
        $this->assertInstanceOf(ProductListingImportCommandFactory::class, $this->factory);
    }

    public function testItReturnsAnAddProductListingCommand()
    {
        $stubProductListingCriteria = $this->getMock(ProductListingCriteria::class, [], [], '', false);
        $commands = $this->factory->createProductListingImportCommands($stubProductListingCriteria);
        $this->assertInternalType('array', $commands);
        $this->assertContainsOnlyInstancesOf(Command::class, $commands);
        $this->assertContainsInstanceOf(AddProductListingCommand::class, $commands);
    }
}
