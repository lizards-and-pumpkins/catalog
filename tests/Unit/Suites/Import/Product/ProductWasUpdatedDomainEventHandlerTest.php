<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Product\Exception\NoProductWasUpdatedDomainEventMessageException;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\SimpleProduct
 * @uses   \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\RehydrateableProductTrait
 */
class ProductWasUpdatedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductProjector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductProjector;

    /**
     * @var ProductWasUpdatedDomainEventHandler
     */
    private $domainEventHandler;

    protected function setUp()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('jsonSerialize')->willReturn([DataVersion::CONTEXT_CODE => '123']);

        $product = new SimpleProduct(
            ProductId::fromString('foo'),
            ProductTaxClass::fromString('bar'),
            new ProductAttributeList(),
            new ProductImageList(),
            $stubContext
        );

        $testPayload = ['id' => 'foo', 'product' => $product];

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(Message::class, [], [], '', false);
        $stubDomainEvent->method('getName')->willReturn('product_was_updated_domain_event');
        $stubDomainEvent->method('getPayload')->willReturn(json_encode($testPayload));

        $this->mockProductProjector = $this->getMock(ProductProjector::class, [], [], '', false);

        $this->domainEventHandler = new ProductWasUpdatedDomainEventHandler(
            $stubDomainEvent,
            $this->mockProductProjector
        );
    }

    public function testDomainEventHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testThrowsExceptionIfEventNameDoesNotMatch()
    {
        $this->expectException(NoProductWasUpdatedDomainEventMessageException::class);
        $this->expectExceptionMessage('Expected "product_was_updated" domain event, got "bar_domain_event"');

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(Message::class, [], [], '', false);
        $stubDomainEvent->method('getName')->willReturn('bar_domain_event');

        $this->domainEventHandler = new ProductWasUpdatedDomainEventHandler(
            $stubDomainEvent,
            $this->mockProductProjector
        );
    }

    public function testProductProjectionIsTriggered()
    {
        $this->mockProductProjector->expects($this->once())->method('project');
        $this->domainEventHandler->process();
    }
}
