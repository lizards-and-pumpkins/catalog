<?php

namespace Brera\Product;

/**
 * @covers Brera\Product\CatalogImportDomainEvent
 */
class CatalogImportDomainEventTest extends \PHPUnit_Framework_TestCase
{
    public function testCatalogImportXmlIsReturned()
    {
        $xml = '<?xml version="1.0"?><rootNode></rootNode>';

        $result = (new CatalogImportDomainEvent($xml))->getXml();

        $this->assertEquals($xml, $result);
    }
}
