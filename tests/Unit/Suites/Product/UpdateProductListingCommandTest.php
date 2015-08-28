<?php

namespace Brera\Product;

use Brera\Command;

/**
 * @covers \Brera\Product\UpdateProductListingCommand
 */
class UpdateProductListingCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingMetaInfoSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingMetaInfoSource;

    /**
     * @var UpdateProductListingCommand
     */
    private $command;

    protected function setUp()
    {
        $this->stubProductListingMetaInfoSource = $this->getMock(
            ProductListingMetaInfoSource::class,
            [],
            [],
            '',
            false
        );
        $this->command = new UpdateProductListingCommand($this->stubProductListingMetaInfoSource);
    }

    public function testCommandInterFaceIsImplemented()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testProductListingMetaInfoSourceIsReturned()
    {
        $result = $this->command->getProductListingMetaInfoSource();
        $this->assertSame($this->stubProductListingMetaInfoSource, $result);
    }
}
