<?php

namespace Brera\Product;

use Brera\DomainEventHandler;
use Brera\Queue\Queue;

/**
 * @covers \Brera\Product\ProductStockQuantityChangedDomainEventHandler
 * @uses   \Brera\Product\ProjectProductStockQuantitySnippetCommand
 */
class ProductStockQuantityChangedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductStockQuantityChangedDomainEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEvent;

    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommandQueue;

    /**
     * @var ProductStockQuantityChangedDomainEventHandler
     */
    private $domainEventHandler;

    protected function setUp()
    {
        $this->mockDomainEvent = $this->getMock(ProductStockQuantityChangedDomainEvent::class, [], [], '', false);
        $this->mockCommandQueue = $this->getMock(Queue::class);

        $this->domainEventHandler = new ProductStockQuantityChangedDomainEventHandler(
            $this->mockDomainEvent,
            $this->mockCommandQueue
        );
    }

    public function testDomainEventHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testDomainEventCommandIsPutIntoCommandQueue()
    {
        $this->mockCommandQueue->expects($this->once())
            ->method('add');

        $this->domainEventHandler->process();
    }
}
