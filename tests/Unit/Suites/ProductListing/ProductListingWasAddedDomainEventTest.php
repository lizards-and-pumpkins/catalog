<?php

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;


/**
 * @covers \LizardsAndPumpkins\ProductListing\ProductListingWasAddedDomainEvent
 */
class ProductListingWasAddedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListing|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListing;
    
    /**
     * @var ProductListingWasAddedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        $this->stubProductListing = $this->getMock(ProductListing::class, [], [], '', false);
        $this->domainEvent = new ProductListingWasAddedDomainEvent($this->stubProductListing);
    }

    public function testDomainEventInterFaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testProductListingIsReturned()
    {
        $result = $this->domainEvent->getListingCriteria();
        $this->assertEquals($this->stubProductListing, $result);
    }
}
