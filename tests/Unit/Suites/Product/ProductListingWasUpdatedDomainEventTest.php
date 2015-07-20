<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductListingWasUpdatedDomainEvent
 */
class ProductListingWasUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    public function testProductListingXmlIsReturned()
    {
        $xml = '<?xml version="1.0"?><rootNode></rootNode>';

        $domainEvent = new ProductListingWasUpdatedDomainEvent($xml);
        $result = $domainEvent->getXml();

        $this->assertEquals($xml, $result);
    }
}
