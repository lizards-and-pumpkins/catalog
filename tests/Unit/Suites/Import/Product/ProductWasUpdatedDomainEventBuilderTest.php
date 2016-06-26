<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\SelfContainedContext;
use LizardsAndPumpkins\Import\Product\Exception\NoProductWasUpdatedDomainEventMessageException;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEventBuilder
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Import\Product\RehydrateableProductTrait
 * @uses   \LizardsAndPumpkins\Import\Product\SimpleProduct
 * @uses   \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class ProductWasUpdatedDomainEventBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductWasUpdatedDomainEventBuilder
     */
    private $domainEventBuilder;

    protected function setUp()
    {
        /** @var ProductAvailability|\PHPUnit_Framework_MockObject_MockObject $stubProductAvailability */
        $stubProductAvailability = $this->createMock(ProductAvailability::class);
        $this->domainEventBuilder = new ProductWasUpdatedDomainEventBuilder($stubProductAvailability);
    }

    public function testCanBeRehydratedFromMessage()
    {
        $testProduct = new SimpleProduct(
            ProductId::fromString('foo'),
            ProductTaxClass::fromString('bar'),
            new ProductAttributeList(),
            new ProductImageList(),
            SelfContainedContext::fromArray([DataVersion::CONTEXT_CODE => '123'])
        );
        $domainEvent = new ProductWasUpdatedDomainEvent($testProduct);


        $message = $domainEvent->toMessage();
        $rehydratedEvent = $this->domainEventBuilder->fromMessage($message);

        $this->assertInstanceOf(ProductWasUpdatedDomainEvent::class, $rehydratedEvent);
        $this->assertSame((string) $testProduct->getId(), (string) $rehydratedEvent->getProduct()->getId());
    }

    public function testThrowsExceptionIfMessageNameDoesNotMatchEventCode()
    {
        $this->expectException(NoProductWasUpdatedDomainEventMessageException::class);
        $this->expectExceptionMessage(sprintf('Expected "product_was_updated" domain event, got "qux"'));
        
        $this->domainEventBuilder->fromMessage(Message::withCurrentTime('qux', [], []));
    }
}
