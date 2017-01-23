<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetProjector;

/**
 * @covers \LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEventHandler
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListing
 * @uses   \LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEvent
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
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
        /** @var ProductListing|\PHPUnit_Framework_MockObject_MockObject $stubProductListing */
        $stubProductListing = $this->createMock(ProductListing::class);
        $stubProductListing->method('serialize')->willReturn(serialize($stubProductListing));
        $stubProductListing->method('getContextData')->willReturn([DataVersion::CONTEXT_CODE => 'foo']);
        
        $this->mockProjector = $this->createMock(ProductListingSnippetProjector::class);

        $message = (new ProductListingWasAddedDomainEvent($stubProductListing))->toMessage();
        $this->domainEventHandler = new ProductListingWasAddedDomainEventHandler($message, $this->mockProjector);
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
