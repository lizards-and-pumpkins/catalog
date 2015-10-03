<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DomainEventHandler;

/**
 * @covers \LizardsAndPumpkins\Product\ProductWasUpdatedDomainEventHandler
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
        $stubProduct = $this->getMock(SimpleProduct::class, [], [], '', false);

        $stubDomainEvent = $this->getMock(ProductWasUpdatedDomainEvent::class, [], [], '', false);
        $stubDomainEvent->method('getProductBuilder')->willReturn($stubProduct);

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

    public function testProductProjectionIsTriggered()
    {
        $this->mockProductProjector->expects($this->once())->method('project');
        $this->domainEventHandler->process();
    }
}
