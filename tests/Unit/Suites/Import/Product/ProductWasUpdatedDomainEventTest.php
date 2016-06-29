<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\SelfContainedContext;
use LizardsAndPumpkins\Import\Product\Exception\NoProductWasUpdatedDomainEventMessageException;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;

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
class ProductWasUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        /** @var ProductAvailability|\PHPUnit_Framework_MockObject_MockObject $stubAvailability */
        $stubAvailability = $this->createMock(ProductAvailability::class);

        $this->testProduct = new SimpleProduct(
            ProductId::fromString('foo'),
            ProductTaxClass::fromString('bar'),
            new ProductAttributeList(),
            new ProductImageList(),
            SelfContainedContext::fromArray([DataVersion::CONTEXT_CODE => $this->testDataVersionString]),
            $stubAvailability
        );
        $this->domainEvent = new ProductWasUpdatedDomainEvent($this->testProduct);
    }

    public function testDomainEventInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testProductIsReturned()
    {
        $result = $this->domainEvent->getProduct();
        $this->assertSame($this->testProduct, $result);
    }

    public function testReturnsProductWasUpdatedMessage()
    {
        $message = $this->domainEvent->toMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(ProductWasUpdatedDomainEvent::CODE, $message->getName());
    }

    public function testReturnsMessageWithProductPayload()
    {
        $message = $this->domainEvent->toMessage();
        $payload = $message->getPayload();
        $this->assertArrayHasKey('id', $payload);
        $this->assertArrayHasKey('product', $payload);
    }

    public function testReturnsDataVersion()
    {
        $dataVersion = $this->domainEvent->getDataVersion();
        $this->assertInstanceOf(DataVersion::class, $dataVersion);
        $this->assertSame($this->testDataVersionString, (string) $dataVersion);
    }

    public function testExceptionIsThrownDuringAttemptToRehydrateProductFromWrongMessageType()
    {
        $this->expectException(NoProductWasUpdatedDomainEventMessageException::class);
        $this->expectExceptionMessage(sprintf('Expected "product_was_updated" domain event, got "qux"'));

        /** @var ProductAvailability|\PHPUnit_Framework_MockObject_MockObject $stubAvailability */
        $stubAvailability = $this->createMock(ProductAvailability::class);
        $testMessage = Message::withCurrentTime('qux', [], []);

        ProductWasUpdatedDomainEvent::rehydrateProduct($testMessage, $stubAvailability);
    }

    public function testDomainEventCanBeRehydratedFromUpdateProductCommandMessage()
    {
        /** @var ProductAvailability|\PHPUnit_Framework_MockObject_MockObject $stubAvailability */
        $stubAvailability = $this->createMock(ProductAvailability::class);

        $testProduct = new SimpleProduct(
            ProductId::fromString('foo'),
            ProductTaxClass::fromString('bar'),
            new ProductAttributeList(),
            new ProductImageList(),
            SelfContainedContext::fromArray([DataVersion::CONTEXT_CODE => '123']),
            $stubAvailability
        );

        $testDomainEvent = new ProductWasUpdatedDomainEvent($testProduct);
        $testMessage = $testDomainEvent->toMessage();

        /** @var ProductAvailability|\PHPUnit_Framework_MockObject_MockObject $stubAvailability */
        $stubAvailability = $this->createMock(ProductAvailability::class);

        $result = ProductWasUpdatedDomainEvent::rehydrateProduct($testMessage, $stubAvailability);;

        $this->assertSame((string) $testProduct->getId(), (string) $result->getId());
    }
}
