<?php

namespace Brera\Product;

use Brera\CommandHandler;
use Brera\Queue\Queue;

/**
 * @covers \Brera\Product\UpdateProductStockQuantityCommandHandler
 * @uses   \Brera\Product\UpdateProductStockQuantityCommand
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
     * @var UpdateProductStockQuantityCommandHandler
     */
    private $commandHandler;

    protected function setUp()
    {
        $this->mockCommand = $this->getMock(UpdateProductStockQuantityCommand::class, [], [], '', false);
        $this->mockDomainEventQueue = $this->getMock(Queue::class);

        $this->commandHandler = new UpdateProductStockQuantityCommandHandler(
            $this->mockCommand,
            $this->mockDomainEventQueue
        );
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testDomainEventCommandIsPutIntoCommandQueue()
    {
        $this->mockDomainEventQueue->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(ProductStockQuantityUpdatedDomainEvent::class));
        $this->commandHandler->process();
    }
}
