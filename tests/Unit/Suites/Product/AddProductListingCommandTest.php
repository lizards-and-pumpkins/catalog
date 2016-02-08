<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Command;

/**
 * @covers \LizardsAndPumpkins\Product\AddProductListingCommand
 */
class AddProductListingCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListing|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListing;

    /**
     * @var AddProductListingCommand
     */
    private $command;

    protected function setUp()
    {
        $this->stubProductListing = $this->getMock(ProductListing::class, [], [], '', false);
        $this->command = new AddProductListingCommand($this->stubProductListing);
    }

    public function testCommandInterFaceIsImplemented()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testProductListingIsReturned()
    {
        $this->assertSame($this->stubProductListing, $this->command->getProductListing());
    }
}
