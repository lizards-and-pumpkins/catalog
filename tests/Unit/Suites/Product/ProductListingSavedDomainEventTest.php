<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductListingSavedDomainEvent
 */
class ProductListingSavedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnProductListingXml()
    {
        $xml = '<?xml version="1.0"?><rootNode></rootNode>';

        $domainEvent = new ProductListingSavedDomainEvent($xml);
        $result = $domainEvent->getXml();

        $this->assertEquals($xml, $result);
    }
}
