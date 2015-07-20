<?php

namespace Brera\Product;

use Brera\DomainEventHandler;

/**
 * @covers \Brera\Product\ProductListingWasUpdatedDomainEventHandler
 */
class ProductListingWasUpdatedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingProjector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProjector;

    /**
     * @var ProductListingWasUpdatedDomainEventHandler
     */
    private $domainEventHandler;

    protected function setUp()
    {
        $stubProductListingSource = $this->getMock(ProductListingSource::class, [], [], '', false);
        $mockDomainEvent = $this->getMock(ProductListingWasUpdatedDomainEvent::class, [], [], '', false);
        $mockDomainEvent->method('getProductListingSource')->willReturn($stubProductListingSource);

        $this->mockProjector = $this->getMock(ProductListingProjector::class, [], [], '', false);

        $this->domainEventHandler = new ProductListingWasUpdatedDomainEventHandler(
            $mockDomainEvent,
            $this->mockProjector
        );
    }

    public function testDomainHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testProductListingProjectionIsTriggered()
    {
        $this->mockProjector->expects($this->once())->method('project');
        $this->domainEventHandler->process();
    }
}
