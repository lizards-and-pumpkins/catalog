<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\MasterFactory;
use LizardsAndPumpkins\Product\ProductListing;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\ProductListingImportCommandLocator
 */
class ProductListingImportCommandLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingImportCommandFactory|MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductListingImportCommandFactory;

    /**
     * @var ProductListingImportCommandLocator
     */
    private $locator;

    protected function setUp()
    {
        $this->mockProductListingImportCommandFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge(get_class_methods(MasterFactory::class), ['createProductListingImportCommands']))
            ->getMock();
        $this->locator = new ProductListingImportCommandLocator($this->mockProductListingImportCommandFactory);
    }

    public function testItDelegatesToTheFactoryToCreateTheProductListingImportCommands()
    {
        $stubCommand = $this->getMock(Command::class);
        $this->mockProductListingImportCommandFactory->expects($this->once())
            ->method('createProductListingImportCommands')
            ->willReturn([$stubCommand]);

        $stubProductListing = $this->getMock(ProductListing::class, [], [], '', false);
        $result = $this->locator->getProductListingImportCommands($stubProductListing);

        $this->assertSame([$stubCommand], $result);
    }
}
