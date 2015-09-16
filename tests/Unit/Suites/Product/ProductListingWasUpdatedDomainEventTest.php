<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DomainEvent;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingWasUpdatedDomainEvent
 */
class ProductListingWasUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingMetaInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingMetaInfo;

    /**
     * @var ProductListingWasUpdatedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        $dummyUrlKey = 'foo';
        $this->stubProductListingMetaInfo = $this->getMock(
            ProductListingMetaInfo::class,
            [],
            [],
            '',
            false
        );
        $this->domainEvent = new ProductListingWasUpdatedDomainEvent(
            $dummyUrlKey,
            $this->stubProductListingMetaInfo
        );
    }

    public function testDomainEventInterFaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testProductListingMetaInfoIsReturned()
    {
        $result = $this->domainEvent->getProductListingMetaInfo();
        $this->assertEquals($this->stubProductListingMetaInfo, $result);
    }
}
