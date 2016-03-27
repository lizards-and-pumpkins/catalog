<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\UpdateProductCommand;
use LizardsAndPumpkins\Messaging\Command\Command;

/**
 * @covers \LizardsAndPumpkins\Import\Product\UpdateProductCommand
 */
class UpdateProductCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProduct;

    /**
     * @var UpdateProductCommand
     */
    private $command;

    protected function setUp()
    {
        $this->stubProduct = $this->getMock(Product::class);
        $this->command = new UpdateProductCommand($this->stubProduct);
    }

    public function testCommandInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testProductBuilderIsReturned()
    {
        $this->assertSame($this->stubProduct, $this->command->getProduct());
    }
}
