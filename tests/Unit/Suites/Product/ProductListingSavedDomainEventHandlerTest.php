<?php

namespace Brera\Product;

use Brera\SampleContextSource;

/**
 * @covers \Brera\Product\ProductListingSavedDomainEventHandler
 */
class ProductListingSavedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testProjectionIsTriggered()
    {
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $stubProductListingSource = $this->getMock(ProductListingSource::class, [], [], '', false);
        $stubDomainEvent = $this->getMock(ProductListingSavedDomainEvent::class, [], [], '', false);

        $mockProductListingSourceBuilder = $this->getMock(ProductListingSourceBuilder::class);
        $mockProductListingSourceBuilder->method('createProductListingSourceFromXml')
            ->willReturn($stubProductListingSource);

        $mockProjector = $this->getMock(ProductListingProjector::class, [], [], '', false);
        $mockProjector->expects($this->once())->method('project');

        (new ProductListingSavedDomainEventHandler(
            $stubDomainEvent,
            $mockProductListingSourceBuilder,
            $mockProjector,
            $stubContextSource
        ))->process();
    }
}
