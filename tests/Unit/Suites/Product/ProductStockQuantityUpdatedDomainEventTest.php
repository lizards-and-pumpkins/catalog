<?php

namespace Brera\Product;

use Brera\DomainEvent;

/**
 * @covers \Brera\Product\ProductStockQuantityUpdatedDomainEvent
 */
class ProductStockQuantityUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductStockQuantitySource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductStockQuantitySource;

    /**
     * @var ProductStockQuantityUpdatedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $this->stubProductStockQuantitySource = $this->getMock(ProductStockQuantitySource::class, [], [], '', false);

        $this->domainEvent = new ProductStockQuantityUpdatedDomainEvent(
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
