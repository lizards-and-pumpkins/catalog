<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Listing;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\ProductListing\Import\ProductListingImportCommandFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\Listing\ProductListingImportCommandLocator
 */
class ProductListingImportCommandLocatorTest extends TestCase
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
        $stubCommand = $this->createMock(Command::class);
        $this->mockProductListingImportCommandFactory->expects($this->once())
            ->method('createProductListingImportCommands')
            ->willReturn([$stubCommand]);

        $stubProductListing = $this->createMock(ProductListing::class);
        $result = $this->locator->getProductListingImportCommands($stubProductListing);

        $this->assertSame([$stubCommand], $result);
    }
}
