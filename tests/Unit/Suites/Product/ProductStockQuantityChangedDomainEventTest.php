<?php

namespace Brera\Product;

use Brera\DomainEvent;

/**
 * @covers \Brera\Product\ProductStockQuantityChangedDomainEvent
 */
class ProductStockQuantityChangedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $dummyPayload = 'foo';

    /**
     * @var ProductStockQuantityChangedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        $this->domainEvent = new ProductStockQuantityChangedDomainEvent($this->dummyPayload);
    }

    public function testDomainEventInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testEventPayloadIsReturned()
    {
        $result = $this->domainEvent->getPayload();
        $this->assertSame($this->dummyPayload, $result);
    }
}
