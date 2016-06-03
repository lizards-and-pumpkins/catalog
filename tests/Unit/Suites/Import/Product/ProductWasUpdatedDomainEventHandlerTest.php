<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\SelfContainedContext;
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
 * @uses   \LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEvent
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
        $product = new SimpleProduct(
            ProductId::fromString('foo'),
            ProductTaxClass::fromString('bar'),
            new ProductAttributeList(),
            new ProductImageList(),
            SelfContainedContext::fromArray([DataVersion::CONTEXT_CODE => '123'])
        );

        $testPayload = ['id' => 'foo', 'product' => $product];

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubMessage */
        $stubMessage = $this->getMock(Message::class, [], [], '', false);
        $stubMessage->method('getName')->willReturn('product_was_updated');
        $stubMessage->method('getPayload')->willReturn(json_encode($testPayload));

        $this->mockProductProjector = $this->getMock(ProductProjector::class, [], [], '', false);

        $this->domainEventHandler = new ProductWasUpdatedDomainEventHandler(
            $stubMessage,
            $this->mockProductProjector
        );
    }

    public function testDomainEventHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testProductProjectionIsTriggered()
    {
        $this->mockProductProjector->expects($this->once())->method('project');
        $this->domainEventHandler->process();
    }
}
