<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\SelfContainedContext;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;

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
     * @var ProductAvailability|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubAvailability;

    /**
     * @var ProductWasUpdatedDomainEventBuilder
     */
    private $domainEventBuilder;

    protected function setUp()
    {
        $this->stubAvailability = $this->createMock(ProductAvailability::class);
        $this->domainEventBuilder = new ProductWasUpdatedDomainEventBuilder($this->stubAvailability);
    }

    public function testDomainEventBuilderInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEventBuilder::class, $this->domainEventBuilder);
    }

    public function testProductWasUpdatedDomainEventIsReturned()
    {
        $testProduct = new SimpleProduct(
            ProductId::fromString('foo'),
            ProductTaxClass::fromString('bar'),
            new ProductAttributeList(),
            new ProductImageList(),
            SelfContainedContext::fromArray([DataVersion::CONTEXT_CODE => '123']),
            $this->stubAvailability
        );

        $testDomainEvent = new ProductWasUpdatedDomainEvent($testProduct);
        $testMessage = $testDomainEvent->toMessage();

        $result = $this->domainEventBuilder->fromMessage($testMessage);

        $this->assertInstanceOf(ProductWasUpdatedDomainEvent::class, $result);
    }
}
