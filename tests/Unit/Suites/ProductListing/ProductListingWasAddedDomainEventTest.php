<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\ProductListing\Exception\NoProductListingWasAddedDomainEventMessage;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEvent
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListing
 */
class ProductListingWasAddedDomainEventTest extends TestCase
{
    private $testDataVersion = '321';

    /**
     * @var ProductListing|MockObject
     */
    private $stubProductListing;
    
    /**
     * @var ProductListingWasAddedDomainEvent
     */
    private $domainEvent;

    final protected function setUp(): void
    {
        $this->stubProductListing = $this->createMock(ProductListing::class);
        $this->stubProductListing->method('serialize')->willReturn(serialize($this->stubProductListing));
        $this->stubProductListing->method('getContextData')
            ->willReturn([DataVersion::CONTEXT_CODE => $this->testDataVersion]);
        $this->domainEvent = new ProductListingWasAddedDomainEvent($this->stubProductListing);
    }

    public function testDomainEventInterFaceIsImplemented(): void
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testProductListingIsReturned(): void
    {
        $result = $this->domainEvent->getListingCriteria();
        $this->assertEquals($this->stubProductListing, $result);
    }

    public function testReturnsMessageWithDomainEventName(): void
    {
        $message = $this->domainEvent->toMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(ProductListingWasAddedDomainEvent::CODE, $message->getName());
    }

    public function testReturnsMessageWithPayload(): void
    {
        $message = $this->domainEvent->toMessage();
        $this->assertArrayHasKey('listing', $message->getPayload());
    }

    public function testReturnsMessageWithDataVersionInMetaData(): void
    {
        $message = $this->domainEvent->toMessage();
        $metaData = $message->getMetadata();
        $this->assertArrayHasKey('data_version', $metaData);
        $this->assertSame($this->testDataVersion, $metaData['data_version']);
    }

    public function testCanBeRehydratedFromMessage(): void
    {
        $message = $this->domainEvent->toMessage();
        $rehydratedEvent = ProductListingWasAddedDomainEvent::fromMessage($message);
        $this->assertInstanceOf(ProductListingWasAddedDomainEvent::class, $rehydratedEvent);
    }

    public function testThrowsExceptionIfMessageNameDoesNotMatchEventCode(): void
    {
        $this->expectException(NoProductListingWasAddedDomainEventMessage::class);
        $this->expectExceptionMessage('Expected "product_listing_was_added" domain event, got "foo"');

        ProductListingWasAddedDomainEvent::fromMessage(Message::withCurrentTime('foo', [], []));
    }

    public function testReturnsTheDataVersion(): void
    {
        $dataVersion = $this->domainEvent->getDataVersion();
        $this->assertInstanceOf(DataVersion::class, $dataVersion);
    }
}
