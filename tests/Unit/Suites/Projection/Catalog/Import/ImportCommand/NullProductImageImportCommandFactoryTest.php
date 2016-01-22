<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand;

use LizardsAndPumpkins\DataVersion;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\NullProductImageImportCommandFactory
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
