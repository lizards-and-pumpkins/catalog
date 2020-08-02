<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use LizardsAndPumpkins\ProductListing\Import\ProductListingProjector;
use PHPUnit\Framework\TestCase;

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
class ProductListingWasAddedDomainEventHandlerTest extends TestCase
{
    /**
     * @var ProductListingProjector|MockObject
     */
    private $mockProjector;

    /**
     * @var ProductListingWasAddedDomainEventHandler
     */
    private $domainEventHandler;

    private function createTestMessage(): Message
    {
        /** @var ProductListing|MockObject $stubProductListing */
        $stubProductListing = $this->createMock(ProductListing::class);
        $stubProductListing->method('serialize')->willReturn(serialize($stubProductListing));
        $stubProductListing->method('getContextData')->willReturn([DataVersion::CONTEXT_CODE => 'foo']);
        return (new ProductListingWasAddedDomainEvent($stubProductListing))->toMessage();
    }

    final protected function setUp(): void
    {
        
        $this->mockProjector = $this->createMock(ProductListingProjector::class);

        $this->domainEventHandler = new ProductListingWasAddedDomainEventHandler($this->mockProjector);
    }

    public function testDomainHandlerInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testProductListingProjectionIsTriggered(): void
    {
        $this->mockProjector->expects($this->once())->method('project');
        
        $this->domainEventHandler->process($this->createTestMessage());
    }
}
