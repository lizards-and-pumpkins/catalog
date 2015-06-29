<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductListingSavedDomainEventHandler
 */
class ProductListingSavedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testProjectionIsTriggered()
    {
        $stubProductListingSource = $this->getMock(ProductListingSource::class, [], [], '', false);

        $mockDomainEvent = $this->getMock(ProductListingSavedDomainEvent::class, [], [], '', false);
        $mockDomainEvent->expects($this->once())
            ->method('getXml');

        $mockProductListingSourceBuilder = $this->getMock(ProductListingSourceBuilder::class);
        $mockProductListingSourceBuilder->expects($this->once())
            ->method('createProductListingSourceFromXml')
            ->willReturn($stubProductListingSource);

        $mockProjector = $this->getMock(ProductListingProjector::class, [], [], '', false);
        $mockProjector->expects($this->once())
            ->method('project');

        (new ProductListingSavedDomainEventHandler(
            $mockDomainEvent,
            $mockProductListingSourceBuilder,
            $mockProjector
        ))->process();
    }
}
