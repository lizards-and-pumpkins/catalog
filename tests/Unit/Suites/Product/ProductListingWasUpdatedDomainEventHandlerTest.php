<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductListingWasUpdatedDomainEventHandler
 */
class ProductListingWasUpdatedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testProjectionIsTriggered()
    {
        $stubProductListingSource = $this->getMock(ProductListingSource::class, [], [], '', false);

        $mockDomainEvent = $this->getMock(ProductListingWasUpdatedDomainEvent::class, [], [], '', false);
        $mockDomainEvent->expects($this->once())->method('getXml');

        $mockProductListingSourceBuilder = $this->getMock(ProductListingSourceBuilder::class);
        $mockProductListingSourceBuilder->expects($this->once())
            ->method('createProductListingSourceFromXml')
            ->willReturn($stubProductListingSource);

        $mockProjector = $this->getMock(ProductListingProjector::class, [], [], '', false);
        $mockProjector->expects($this->once())->method('project');

        (new ProductListingWasUpdatedDomainEventHandler(
            $mockDomainEvent,
            $mockProductListingSourceBuilder,
            $mockProjector
        ))->process();
    }
}
