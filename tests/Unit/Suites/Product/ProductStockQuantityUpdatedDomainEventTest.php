<?php

namespace Brera\Product;

use Brera\DomainEvent;

/**
 * @covers \Brera\Product\ProductStockQuantityUpdatedDomainEvent
 */
class ProductStockQuantityUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $dummyPayload = 'foo';

    /**
     * @var ProductStockQuantityUpdatedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        $this->domainEvent = new ProductStockQuantityUpdatedDomainEvent($this->dummyPayload);
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
