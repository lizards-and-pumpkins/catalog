<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductWasUpdatedDomainEvent
 */
class ProductWasUpdatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
    public function testProductImportXmlIsReturned()
    {
        $xml = '<?xml version="1.0"?><rootNode></rootNode>';

        $domainEvent = new ProductWasUpdatedDomainEvent($xml);
        $result = $domainEvent->getXml();

        $this->assertEquals($xml, $result);
    }
}
