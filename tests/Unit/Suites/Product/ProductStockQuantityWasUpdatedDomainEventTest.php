<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DomainEvent;

/**
 * @covers \LizardsAndPumpkins\Product\ProductStockQuantityWasUpdatedDomainEvent
 */
class ProductStockQuantityWasUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductStockQuantitySource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductStockQuantitySource;

    /**
     * @var ProductStockQuantityWasUpdatedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $this->stubProductStockQuantitySource = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);

        $this->domainEvent = new ProductStockQuantityWasUpdatedDomainEvent(
            $stubProductId,
            $this->stubProductStockQuantitySource
        );
    }

    public function testDomainEventInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testProductStockQuantitySourceIsReturned()
    {
        $result = $this->domainEvent->getProductStockQuantitySource();
        $this->assertSame($this->stubProductStockQuantitySource, $result);
    }
}
