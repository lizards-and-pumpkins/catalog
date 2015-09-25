<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DomainEvent;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingWasAddedDomainEvent
 */
class ProductListingWasAddedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingCriteria;
    
    /**
     * @var ProductListingWasAddedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        $this->stubProductListingCriteria = $this->getMock(ProductListingCriteria::class, [], [], '', false);
        $this->domainEvent = new ProductListingWasAddedDomainEvent($this->stubProductListingCriteria);
    }

    public function testDomainEventInterFaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testProductListingCriteriaIsReturned()
    {
        $result = $this->domainEvent->getProductListingCriteria();
        $this->assertEquals($this->stubProductListingCriteria, $result);
    }
}
