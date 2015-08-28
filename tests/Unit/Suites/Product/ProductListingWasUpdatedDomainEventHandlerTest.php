<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DomainEventHandler;

/**
 * @covers \Brera\Product\ProductListingWasUpdatedDomainEventHandler
 */
class ProductListingWasUpdatedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingMetaInfoSnippetProjector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProjector;

    /**
     * @var ProductListingWasUpdatedDomainEventHandler
     */
    private $domainEventHandler;

    protected function setUp()
    {
        $stubProductListingMetaInfoSource = $this->getMock(ProductListingMetaInfoSource::class, [], [], '', false);

        /** @var ProductListingWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $mockDomainEvent */
        $mockDomainEvent = $this->getMock(ProductListingWasUpdatedDomainEvent::class, [], [], '', false);
        $mockDomainEvent->method('getProductListingMetaInfoSource')->willReturn($stubProductListingMetaInfoSource);

        $this->mockProjector = $this->getMock(ProductListingMetaInfoSnippetProjector::class, [], [], '', false);

        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);

        $this->domainEventHandler = new ProductListingWasUpdatedDomainEventHandler(
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
