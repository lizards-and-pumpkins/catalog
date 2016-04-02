<?php

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\ProductListing\AddProductListingCommand;


/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory
 * @uses   \LizardsAndPumpkins\ProductListing\AddProductListingCommand
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
        $stubProductListing = $this->getMock(ProductListing::class, [], [], '', false);
        $commands = $this->factory->createProductListingImportCommands($stubProductListing);
        $this->assertInternalType('array', $commands);
        $this->assertContainsOnlyInstancesOf(Command::class, $commands);
        $this->assertContainsInstanceOf(AddProductListingCommand::class, $commands);
    }
}
