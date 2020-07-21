<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Image;

use LizardsAndPumpkins\Import\Image\ProductImageImportCommandFactory;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\Image\ProductImageImportCommandLocator
 */
class ProductImageImportCommandLocatorTest extends TestCase
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
        $stubCommand = $this->createMock(Command::class);
        $stubDataVersion = $this->createMock(DataVersion::class);
        $this->mockProductImageImportCommandFactory->expects($this->once())
            ->method('createProductImageImportCommands')
            ->willReturn([$stubCommand]);

        $result = $this->locator->getProductImageImportCommands('file.jpg', $stubDataVersion);

        $this->assertSame([$stubCommand], $result);
    }
}
