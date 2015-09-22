<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DomainEventHandler;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingWasAddedDomainEventHandler
 */
class ProductListingWasAddedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingMetaInfoSnippetProjector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProjector;

    /**
     * @var ProductListingWasAddedDomainEventHandler
     */
    private $domainEventHandler;

    protected function setUp()
    {
        $stubProductListingMetaInfo = $this->getMock(ProductListingMetaInfo::class, [], [], '', false);

        /** @var ProductListingWasAddedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $mockDomainEvent */
        $mockDomainEvent = $this->getMock(ProductListingWasAddedDomainEvent::class, [], [], '', false);
        $mockDomainEvent->method('getProductListingMetaInfo')->willReturn($stubProductListingMetaInfo);

        $this->mockProjector = $this->getMock(ProductListingMetaInfoSnippetProjector::class, [], [], '', false);

        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);

        $this->domainEventHandler = new ProductListingWasAddedDomainEventHandler(
            $mockDomainEvent,
            $stubContextSource,
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
