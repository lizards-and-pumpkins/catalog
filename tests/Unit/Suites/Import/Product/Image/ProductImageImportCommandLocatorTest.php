<?php

namespace LizardsAndPumpkins\Import\Product\Image;

use LizardsAndPumpkins\Import\Image\ProductImageImportCommandFactory;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

/**
 * @covers \LizardsAndPumpkins\Import\Product\Image\ProductImageImportCommandLocator
 */
class ProductImageImportCommandLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductImageImportCommandFactory|MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductImageImportCommandFactory;

    /**
     * @var ProductImageImportCommandLocator
     */
    private $locator;

    protected function setUp()
    {
        $this->mockProductImageImportCommandFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge(get_class_methods(MasterFactory::class), ['createProductImageImportCommands']))
            ->getMock();
        $this->locator = new ProductImageImportCommandLocator($this->mockProductImageImportCommandFactory);
    }
    
    public function testItDelegatesToTheFactoryToCreateTheProductImageImportCommands()
    {
        $stubCommand = $this->getMock(Command::class);
        $stubDataVersion = $this->getMock(DataVersion::class, [], [], '', false);
        $this->mockProductImageImportCommandFactory->expects($this->once())
            ->method('createProductImageImportCommands')
            ->willReturn([$stubCommand]);

        $result = $this->locator->getProductImageImportCommands('file.jpg', $stubDataVersion);

        $this->assertSame([$stubCommand], $result);
    }
}
