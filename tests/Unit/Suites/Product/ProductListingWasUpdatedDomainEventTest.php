<?php

namespace Brera\Product;

use Brera\DomainEvent;

/**
 * @covers \Brera\Product\ProductListingWasUpdatedDomainEvent
 */
class ProductListingWasUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingSource;

    /**
     * @var ProductListingWasUpdatedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        $dummyUrlKey = 'foo';
        $this->stubProductListingSource = $this->getMock(ProductListingSource::class, [], [], '', false);
        $this->domainEvent = new ProductListingWasUpdatedDomainEvent($dummyUrlKey, $this->stubProductListingSource);
    }

    public function testDomainEventInterFaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testProductListingSourceIsReturned()
    {
        $result = $this->domainEvent->getProductListingSource();
        $this->assertEquals($this->stubProductListingSource, $result);
    }
}
