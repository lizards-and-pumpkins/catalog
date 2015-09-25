<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DomainEventHandler;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingWasAddedDomainEventHandler
 */
class ProductListingWasAddedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingCriteriaSnippetProjector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProjector;

    /**
     * @var ProductListingWasAddedDomainEventHandler
     */
    private $domainEventHandler;

    protected function setUp()
    {
        $stubProductListingCriteria = $this->getMock(ProductListingCriteria::class, [], [], '', false);

        /** @var ProductListingWasAddedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $mockDomainEvent */
        $mockDomainEvent = $this->getMock(ProductListingWasAddedDomainEvent::class, [], [], '', false);
        $mockDomainEvent->method('getProductListingCriteria')->willReturn($stubProductListingCriteria);

        $this->mockProjector = $this->getMock(ProductListingCriteriaSnippetProjector::class, [], [], '', false);

        $this->domainEventHandler = new ProductListingWasAddedDomainEventHandler(
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
