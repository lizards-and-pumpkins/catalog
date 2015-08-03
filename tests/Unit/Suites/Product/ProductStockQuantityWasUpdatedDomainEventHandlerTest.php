<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DomainEventHandler;

/**
 * @covers \Brera\Product\ProductStockQuantityWasUpdatedDomainEventHandler
 */
class ProductStockQuantityWasUpdatedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductStockQuantityWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEvent;

    /**
     * @var ProductStockQuantityProjector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProjector;

    /**
     * @var ProductStockQuantityWasUpdatedDomainEventHandler
     */
    private $domainEventHandler;

    protected function setUp()
    {
        $this->mockDomainEvent = $this->getMock(ProductStockQuantityWasUpdatedDomainEvent::class, [], [], '', false);
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->mockProjector = $this->getMock(ProductStockQuantityProjector::class, [], [], '', false);

        $this->domainEventHandler = new ProductStockQuantityWasUpdatedDomainEventHandler(
            $this->mockDomainEvent,
            $stubContextSource,
            $this->mockProjector
        );
    }

    public function testDomainEventHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testProductQuantitySnippetProjectionIsTriggered()
    {
        $stubProductStockQuantitySource = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);
        $this->mockDomainEvent->method('getProductStockQuantitySource')->willReturn($stubProductStockQuantitySource);

        $this->mockProjector->expects($this->once())->method('project');

        $this->domainEventHandler->process();
    }
}
