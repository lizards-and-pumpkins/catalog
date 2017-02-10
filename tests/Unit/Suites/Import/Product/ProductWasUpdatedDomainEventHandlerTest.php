<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\SelfContainedContext;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

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
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class ProductWasUpdatedDomainEventHandlerTest extends TestCase
{
    /**
     * @var ProductProjector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductProjector;

    /**
     * @var ProductWasUpdatedDomainEventHandler
     */
    private $domainEventHandler;

    private function createTestMessage(): Message
    {
        $testProduct = new SimpleProduct(
            new ProductId('foo'),
            ProductTaxClass::fromString('bar'),
            new ProductAttributeList(),
            new ProductImageList(),
            new SelfContainedContext([DataVersion::CONTEXT_CODE => '123'])
        );

        return (new ProductWasUpdatedDomainEvent($testProduct))->toMessage();
    }

    protected function setUp()
    {
        $this->mockProductProjector = $this->createMock(ProductProjector::class);

        $this->domainEventHandler = new ProductWasUpdatedDomainEventHandler($this->mockProductProjector);
    }

    public function testDomainEventHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testProductProjectionIsTriggered()
    {
        $this->mockProductProjector->expects($this->once())->method('project');
        $this->domainEventHandler->process($this->createTestMessage());
    }
}
