<?php

namespace Brera\Product;

use Brera\Command;

/**
 * @covers \Brera\Product\UpdateProductListingCommand
 */
class UpdateProductListingCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingSource;

    /**
     * @var UpdateProductListingCommand
     */
    private $command;

    protected function setUp()
    {
        $this->stubProductListingSource = $this->getMock(ProductListingSource::class, [], [], '', false);
        $this->command = new UpdateProductListingCommand($this->stubProductListingSource);
    }

    public function testCommandInterFaceIsImplemented()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testProductListingSourceIsReturned()
    {
        $result = $this->command->getProductListingSource();
        $this->assertSame($this->stubProductListingSource, $result);
    }
}
