<?php

namespace LizardsAndPumpkins\ProductListing\Import;

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
        /** @var ProductListing|\PHPUnit_Framework_MockObject_MockObject $stubProductListing */
        $stubProductListing = $this->createMock(ProductListing::class);
        $commands = $this->factory->createProductListingImportCommands($stubProductListing);
        $this->assertInternalType('array', $commands);
        $this->assertNotEmpty($commands);
        $this->assertContainsOnlyInstancesOf(AddProductListingCommand::class, $commands);
    }
}
