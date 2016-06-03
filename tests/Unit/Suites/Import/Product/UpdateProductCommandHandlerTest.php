<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\Product\UpdateProductCommandHandler
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\SimpleProduct
 * @uses   \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\RehydrateableProductTrait
 * @uses   \LizardsAndPumpkins\Import\Product\UpdateProductCommand
 * @uses   \LizardsAndPumpkins\Import\Product\ProductWasUpdatedDomainEvent
 */
class UpdateProductCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DomainEventQueue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var Product
     */
    private $testProduct;

    /**
     * @var UpdateProductCommandHandler
     */
    private $commandHandler;

    /**
     * @return Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubContext()
    {
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('jsonSerialize')->willReturn([DataVersion::CONTEXT_CODE => '123']);
        return $stubContext;
    }

    protected function setUp()
    {
        $this->testProduct = new SimpleProduct(
            ProductId::fromString('foo'),
            ProductTaxClass::fromString('bar'),
            new ProductAttributeList(),
            new ProductImageList(),
            $this->createStubContext()
        );

        $testPayload = ['id' => $this->testProduct->getId(), 'product' => $this->testProduct];
        
        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubMessage */
        $stubMessage = $this->getMock(Message::class, [], [], '', false);
        $stubMessage->method('getName')->willReturn('update_product');
        $stubMessage->method('getPayload')->willReturn(json_encode($testPayload));

        $this->mockDomainEventQueue = $this->getMock(DomainEventQueue::class, [], [], '', false);

        $this->commandHandler = new UpdateProductCommandHandler($stubMessage, $this->mockDomainEventQueue);
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testProductWasUpdatedDomainEventIsEmitted()
    {
        $this->mockDomainEventQueue->expects($this->once())->method('addVersioned');

        $this->commandHandler->process();
    }
}
