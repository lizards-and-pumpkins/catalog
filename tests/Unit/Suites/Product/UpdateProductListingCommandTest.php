<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Command;

/**
 * @covers \LizardsAndPumpkins\Product\UpdateProductListingCommand
 */
class UpdateProductListingCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingMetaInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingMetaInfo;

    /**
     * @var UpdateProductListingCommand
     */
    private $command;

    protected function setUp()
    {
        $this->stubProductListingMetaInfo = $this->getMock(
            ProductListingMetaInfo::class,
            [],
            [],
            '',
            false
        );
        $this->command = new UpdateProductListingCommand($this->stubProductListingMetaInfo);
    }

    public function testCommandInterFaceIsImplemented()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testProductListingMetaInfoIsReturned()
    {
        $result = $this->command->getProductListingMetaInfo();
        $this->assertSame($this->stubProductListingMetaInfo, $result);
    }
}
