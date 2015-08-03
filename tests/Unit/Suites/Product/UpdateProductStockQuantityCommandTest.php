<?php

namespace Brera\Product;

use Brera\Command;

/**
 * @covers \Brera\Product\UpdateProductStockQuantityCommand
 */
class UpdateProductStockQuantityCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductStockQuantitySource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductStockQuantitySource;

    /**
     * @var UpdateProductStockQuantityCommand
     */
    private $command;

    protected function setUp()
    {
        $this->stubProductStockQuantitySource = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);

        $this->command = new UpdateProductStockQuantityCommand($this->stubProductStockQuantitySource);
    }

    public function testCommandInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testProductStockQuantitySource()
    {
        $result = $this->command->getProductStockQuantitySource();
        $this->assertSame($this->stubProductStockQuantitySource, $result);
    }
}
