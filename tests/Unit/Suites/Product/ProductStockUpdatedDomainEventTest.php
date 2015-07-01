<?php

namespace Brera\Product;

use Brera\DomainEvent;

/**
 * @covers \Brera\Product\ProductStockUpdatedDomainEvent
 */
class ProductStockUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductStockUpdatedDomainEvent
     */
    private $domainEvent;

    /**
     * @var Sku
     */
    private $stubSku;

    /**
     * @var ProductStockQuantity
     */
    private $stubStock;

    protected function setUp()
    {
        $this->stubSku = $this->getMock(Sku::class);
        $this->stubStock = $this->getMock(ProductStockQuantity::class, [], [], '', false);

        $this->domainEvent = new ProductStockUpdatedDomainEvent($this->stubSku, $this->stubStock);
    }

    public function testDomainEventInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testSkuIsReturned()
    {
        $result = $this->domainEvent->getSku();
        $this->assertSame($this->stubSku, $result);
    }

    public function testStockIsReturned()
    {
        $result = $this->domainEvent->getStock();
        $this->assertSame($this->stubStock, $result);
    }
}
