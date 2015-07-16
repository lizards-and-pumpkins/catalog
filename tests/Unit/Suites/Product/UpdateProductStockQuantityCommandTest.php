<?php

namespace Brera\Product;

use Brera\Command;

/**
 * @covers \Brera\Product\UpdateProductStockQuantityCommand
 */
class UpdateProductStockQuantityCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductId|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductId;

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
        $this->stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $this->stubProductStockQuantitySource = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);

        $this->command = new UpdateProductStockQuantityCommand(
            $this->stubProductId,
            $this->stubProductStockQuantitySource
        );
    }

    public function testCommandInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testProductIdIsReturned()
    {
        $result = $this->command->getProductId();
        $this->assertSame($this->stubProductId, $result);
    }

    public function testProductStockQuantitySource()
    {
        $result = $this->command->getProductStockQuantitySource();
        $this->assertSame($this->stubProductStockQuantitySource, $result);
    }
}
