<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Command;

/**
 * @covers \LizardsAndPumpkins\Product\UpdateMultipleProductStockQuantityCommand
 */
class UpdateMultipleProductStockQuantityCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateMultipleProductStockQuantityCommand
     */
    private $command;

    protected function setUp()
    {
        $stubProductStockQuantitySourceArray = [$this->getMock(ProductStockQuantitySource::class, [], [], '', false)];
        $this->command = new UpdateMultipleProductStockQuantityCommand($stubProductStockQuantitySourceArray);
    }

    public function testCommandInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testArrayOrProductStockQuantitySourcesIsReturned()
    {
        $result = $this->command->getProductStockQuantitySourceArray();
        $this->assertContainsOnly(ProductStockQuantitySource::class, $result);
    }
}
