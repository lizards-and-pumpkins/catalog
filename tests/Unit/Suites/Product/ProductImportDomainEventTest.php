<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductImportDomainEvent
 */
class ProductImportDomainEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnProductImportXml()
    {
        $xml = '<?xml version="1.0"?><rootNode></rootNode>';

        $domainEvent = new ProductImportDomainEvent($xml);
        $result = $domainEvent->getXml();

        $this->assertEquals($xml, $result);
    }
}
