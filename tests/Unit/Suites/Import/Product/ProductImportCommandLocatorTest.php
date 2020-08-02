<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\ProductDetail\Import\ProductImportCommandFactory;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductImportCommandLocator
 */
class ProductImportCommandLocatorTest extends TestCase
{
    /**
     * @var ProductImportCommandFactory|MasterFactory|MockObject
     */
    private $mockProductImportCommandFactory;

    /**
     * @var ProductImportCommandLocator
     */
    private $locator;

    final protected function setUp(): void
    {
        $this->mockProductImportCommandFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge(get_class_methods(MasterFactory::class), ['createProductImportCommands']))
            ->getMock();
        $this->locator = new ProductImportCommandLocator($this->mockProductImportCommandFactory);
    }

    public function testItDelegatesToTheFactoryToCreateTheProductImportCommands(): void
    {
        $stubCommand = $this->createMock(Command::class);
        $this->mockProductImportCommandFactory->expects($this->once())
            ->method('createProductImportCommands')
            ->willReturn([$stubCommand]);
        
        $result = $this->locator->getProductImportCommands($this->createMock(Product::class));
        
        $this->assertSame([$stubCommand], $result);
    }
}
