<?php

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetProjector;


/**
 * @covers \LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler
 */
class ProductListingWasAddedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingSnippetProjector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProjector;

    /**
     * @var ProductListingWasAddedDomainEventHandler
     */
    private $domainEventHandler;

    protected function setUp()
    {
        $stubProductListing = $this->getMock(ProductListing::class, [], [], '', false);

        /** @var ProductListingWasAddedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $mockDomainEvent */
        $mockDomainEvent = $this->getMock(ProductListingWasAddedDomainEvent::class, [], [], '', false);
        $mockDomainEvent->method('getProductListing')->willReturn($stubProductListing);

        $this->mockProjector = $this->getMock(ProductListingSnippetProjector::class, [], [], '', false);

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
