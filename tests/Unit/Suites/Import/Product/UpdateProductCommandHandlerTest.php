<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\DataVersion\ContextVersion;
use LizardsAndPumpkins\Import\Product\Exception\NoUpdateProductCommandMessageException;
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
 */
class UpdateProductCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DomainEventQueue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var UpdateProductCommandHandler
     */
    private $commandHandler;

    /**
     * @var Product
     */
    private $testProduct;

    /**
     * @return Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubContext()
    {
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('jsonSerialize')->willReturn([ContextVersion::CODE => '123']);
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
        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(Message::class, [], [], '', false);
        $stubCommand->method('getName')->willReturn('update_product_command');
        $stubCommand->method('getPayload')->willReturn(json_encode($testPayload));

        $this->mockDomainEventQueue = $this->getMock(DomainEventQueue::class, [], [], '', false);

        $this->commandHandler = new UpdateProductCommandHandler($stubCommand, $this->mockDomainEventQueue);
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testThrowsExceptionIfCommandNameDoesNotMatch()
    {
        $this->expectException(NoUpdateProductCommandMessageException::class);
        $this->expectExceptionMessage('Expected "update_product" command, got "bar_command"');

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $invalidCommand */
        $invalidCommand = $this->getMock(Message::class, [], [], '', false);
        $invalidCommand->method('getName')->willReturn('bar_command');

        new UpdateProductCommandHandler($invalidCommand, $this->mockDomainEventQueue);
    }

    public function testProductWasUpdatedDomainEventIsEmitted()
    {
        $expectedPayload = json_encode(['id' => $this->testProduct->getId(), 'product' => $this->testProduct]);
        $this->mockDomainEventQueue->expects($this->once())->method('addVersioned')
            ->with('product_was_updated', $expectedPayload, $this->anything());

        $this->commandHandler->process();
    }
}
