<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\DataVersion;

/**
 * @covers \LizardsAndPumpkins\Product\AddProductListingCommand
 */
class AddProductListingCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingCriteria;

    /**
     * @var AddProductListingCommand
     */
    private $command;

    protected function setUp()
    {
        $this->stubProductListingCriteria = $this->getMock(ProductListingCriteria::class, [], [], '', false);
        $this->command = new AddProductListingCommand($this->stubProductListingCriteria);
    }

    public function testCommandInterFaceIsImplemented()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testProductListingCriteriaIsReturned()
    {
        $result = $this->command->getProductListingCriteria();
        $this->assertSame($this->stubProductListingCriteria, $result);
    }
}
