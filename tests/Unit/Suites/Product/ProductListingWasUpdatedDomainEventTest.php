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
    private $stubProductListingMetaInfoSource;

    /**
     * @var ProductListingWasUpdatedDomainEvent
     */
    private $domainEvent;

    protected function setUp()
    {
        $dummyUrlKey = 'foo';
        $this->stubProductListingMetaInfoSource = $this->getMock(
            ProductListingMetaInfo::class,
            [],
            [],
            '',
            false
        );
        $this->domainEvent = new ProductListingWasUpdatedDomainEvent(
            $dummyUrlKey,
            $this->stubProductListingMetaInfoSource
        );
    }

    public function testDomainEventInterFaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEvent::class, $this->domainEvent);
    }

    public function testProductListingMetaInfoSourceIsReturned()
    {
        $result = $this->domainEvent->getProductListingMetaInfoSource();
        $this->assertEquals($this->stubProductListingMetaInfoSource, $result);
    }
}
