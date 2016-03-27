<?php

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Image\NullProductImageImportCommandFactory;
use LizardsAndPumpkins\Import\Image\ProductImageImportCommandFactory;

/**
 * @covers \LizardsAndPumpkins\Import\Image\NullProductImageImportCommandFactory
 */
class NullProductImageImportCommandFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NullProductImageImportCommandFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new NullProductImageImportCommandFactory();
    }

    public function testItImplementsTheProductImportCommandFactoryInterface()
    {
        $this->assertInstanceOf(ProductImageImportCommandFactory::class, $this->factory);
    }

    public function testItReturnsNoCommands()
    {
        $stubDataVersion = $this->getMock(DataVersion::class, [], [], '', false);
        $this->assertSame([], $this->factory->createProductImageImportCommands('image.jpg', $stubDataVersion));
    }
}
