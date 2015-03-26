<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\Projector;

/**
 * @covers \Brera\Product\ProductListingSavedDomainEventHandler
 */
class ProductListingSavedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldTriggerProjection()
    {
        $stubProductListingSource = $this->getMock(ProductListingSource::class, [], [], '', false);

        $mockDomainEvent = $this->getMock(ProductListingSavedDomainEvent::class, [], [], '', false);
        $mockDomainEvent->expects($this->once())
            ->method('getXml');

        $mockProductListingSourceBuilder = $this->getMock(ProductListingSourceBuilder::class);
        $mockProductListingSourceBuilder->expects($this->once())
            ->method('createProductListingSourceFromXml')
            ->willReturn($stubProductListingSource);

        $stubContextSource = $this->getMockBuilder(ContextSource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getContextMatrix'])
            ->getMock();

        $mockProjector = $this->getMock(Projector::class);
        $mockProjector->expects($this->once())
            ->method('project');

        (new ProductListingSavedDomainEventHandler(
            $mockDomainEvent,
            $mockProductListingSourceBuilder,
            $stubContextSource,
            $mockProjector
        ))->process();
    }
}
