<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\ProductListing\Exception\NoProductListingWasAddedDomainEventMessage;
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
        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubEvent */
        $stubEvent = $this->getMock(Message::class, [], [], '', false);
        $stubEvent->method('getName')->willReturn('product_listing_was_added_domain_event');
        $stubEvent->method('getPayload')->willReturn('');

        $this->mockProjector = $this->getMock(ProductListingSnippetProjector::class, [], [], '', false);

        $this->domainEventHandler = new ProductListingWasAddedDomainEventHandler($stubEvent, $this->mockProjector);
    }

    public function testDomainHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testThrowsExceptionIfDomainEventNameDoesNotMatch()
    {
        $this->expectException(NoProductListingWasAddedDomainEventMessage::class);
        $this->expectExceptionMessage('Expected "product_listing_was_added" domain event, got "bar_buz_domain_event');
        
        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $invalidEvent */
        $invalidEvent = $this->getMock(Message::class, [], [], '', false);
        $invalidEvent->method('getName')->willReturn('bar_buz_domain_event');

        new ProductListingWasAddedDomainEventHandler($invalidEvent, $this->mockProjector);
    }

    public function testProductListingProjectionIsTriggered()
    {
        $this->mockProjector->expects($this->once())->method('project');
        $this->domainEventHandler->process();
    }
}
