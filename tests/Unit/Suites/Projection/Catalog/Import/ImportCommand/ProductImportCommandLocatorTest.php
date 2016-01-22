<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\MasterFactory;
use LizardsAndPumpkins\Product\Product;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\ProductImportCommandLocator
 */
class ProductImportCommandLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductImportCommandFactory|MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductImportCommandFactory;

    /**
     * @var ProductImportCommandLocator
     */
    private $locator;

    protected function setUp()
    {
        $this->mockProductImportCommandFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge(get_class_methods(MasterFactory::class), ['createProductImportCommands']))
            ->getMock();
        $this->locator = new ProductImportCommandLocator($this->mockProductImportCommandFactory);
    }

    public function testItDelegatesToTheFactoryToCreateTheProductImportCommands()
    {
        $stubCommand = $this->getMock(Command::class);
        $this->mockProductImportCommandFactory->expects($this->once())
            ->method('createProductImportCommands')
            ->willReturn([$stubCommand]);
        
        $result = $this->locator->getProductImportCommands($this->getMock(Product::class));
        
        $this->assertSame([$stubCommand], $result);
    }
}
