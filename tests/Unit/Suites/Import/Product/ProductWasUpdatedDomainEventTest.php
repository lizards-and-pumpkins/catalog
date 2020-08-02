<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\SelfContainedContext;
use LizardsAndPumpkins\Import\Product\Exception\NoProductWasUpdatedDomainEventMessageException;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\SimpleProduct
 * @uses   \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\RehydrateableProductTrait
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class ProductWasUpdatedDomainEventTest extends TestCase
{
    private $testDataVersionString = '123';

    /**
     * @var Product
     */
    private $testProduct;

    /**
     * @var ProductWasUpdatedDomainEvent
     */
    private $domainEvent;

    final protected function setUp(): void
    {
        $this->testProduct = new SimpleProduct(
            new ProductId('foo'),
            ProductTaxClass::fromString('bar'),
            new ProductAttributeList(),
            new ProductImageList(),
            new SelfContainedContext([DataVersion::CONTEXT_CODE => $this->testDataVersionString])
        );
        $this->domainEvent = new ProductWasUpdatedDomainEvent($this->testProduct);
    }

    public function testDomainEventInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testProductIsReturned(): void
    {
        $result = $this->domainEvent->getProduct();
        $this->assertSame($this->testProduct, $result);
    }

    public function testReturnsProductWasUpdatedMessage(): void
    {
        $message = $this->domainEvent->toMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(ProductWasUpdatedDomainEvent::CODE, $message->getName());
    }

    public function testReturnsMessageWithProductPayload(): void
    {
        $message = $this->domainEvent->toMessage();
        $payload = $message->getPayload();
        $this->assertArrayHasKey('id', $payload);
        $this->assertArrayHasKey('product', $payload);
    }

    public function testCanBeRehydratedFromMessage(): void
    {
        $message = $this->domainEvent->toMessage();
        $rehydratedEvent = ProductWasUpdatedDomainEvent::fromMessage($message);
        $this->assertInstanceOf(ProductWasUpdatedDomainEvent::class, $rehydratedEvent);
        $this->assertSame((string) $this->testProduct->getId(), (string) $rehydratedEvent->getProduct()->getId());
    }

    public function testThrowsExceptionIfMessageNameDoesNotMatchEventCode(): void
    {
        $this->expectException(NoProductWasUpdatedDomainEventMessageException::class);
        $this->expectExceptionMessage(sprintf('Expected "product_was_updated" domain event, got "qux"'));
        ProductWasUpdatedDomainEvent::fromMessage(Message::withCurrentTime('qux', [], []));
    }

    public function testReturnsDataVersion(): void
    {
        $dataVersion = $this->domainEvent->getDataVersion();
        $this->assertInstanceOf(DataVersion::class, $dataVersion);
        $this->assertSame($this->testDataVersionString, (string) $dataVersion);
    }
}
