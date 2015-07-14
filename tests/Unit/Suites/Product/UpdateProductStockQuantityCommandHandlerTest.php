<?php

namespace Brera\Product;

use Brera\CommandHandler;
use Brera\Queue\Queue;

/**
 * @covers \Brera\Product\UpdateProductStockQuantityCommandHandler
 * @uses   \Brera\Product\UpdateProductStockQuantityCommand
 * @uses   \Brera\Product\ProductId
 * @uses   \Brera\Product\ProductStockQuantityUpdatedDomainEvent
 */
class UpdateProductStockQuantityCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateProductStockQuantityCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommand;

    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var ProductStockQuantitySourceBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductStockQuantitySourceBuilder;

    /**
     * @var UpdateProductStockQuantityCommandHandler
     */
    private $commandHandler;

    protected function setUp()
    {
        $this->mockCommand = $this->getMock(UpdateProductStockQuantityCommand::class, [], [], '', false);
        $this->mockDomainEventQueue = $this->getMock(Queue::class);
        $this->mockProductStockQuantitySourceBuilder = $this->getMock(
            ProductStockQuantitySourceBuilder::class,
            [],
            [],
            '',
            false
        );

        $this->commandHandler = new UpdateProductStockQuantityCommandHandler(
            $this->mockCommand,
            $this->mockDomainEventQueue,
            $this->mockProductStockQuantitySourceBuilder
        );
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testDomainEventCommandIsPutIntoCommandQueue()
    {
        $stubSku = $this->getMock(Sku::class);

        $mockProductStockQuantitySource = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);
        $mockProductStockQuantitySource->method('getSku')->willReturn($stubSku);

        $this->mockProductStockQuantitySourceBuilder->method('createFromXml')
            ->willReturn($mockProductStockQuantitySource);

        $this->mockDomainEventQueue->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(ProductStockQuantityUpdatedDomainEvent::class));
        $this->commandHandler->process();
    }
}
