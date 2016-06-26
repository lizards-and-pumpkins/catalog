<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\SelfContainedContext;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue;

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
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
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

    protected function setUp()
    {
        $this->testProduct = new SimpleProduct(
            ProductId::fromString('foo'),
            ProductTaxClass::fromString('bar'),
            new ProductAttributeList(),
            new ProductImageList(),
            SelfContainedContext::fromArray([DataVersion::CONTEXT_CODE => 'buz'])
        );
        
        $testCommand = new UpdateProductCommand($this->testProduct);

        $this->mockDomainEventQueue = $this->createMock(DomainEventQueue::class);

        /** @var UpdateProductCommandBuilder|\PHPUnit_Framework_MockObject_MockObject $stubCommandBuilder */
        $stubCommandBuilder = $this->createMock(UpdateProductCommandBuilder::class);
        $stubCommandBuilder->method('fromMessage')->willReturn($testCommand);

        $this->commandHandler = new UpdateProductCommandHandler(
            $testCommand->toMessage(),
            $this->mockDomainEventQueue,
            $stubCommandBuilder
        );
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testProductWasUpdatedDomainEventIsEmitted()
    {
        $this->mockDomainEventQueue->expects($this->once())->method('add');

        $this->commandHandler->process();
    }
}
