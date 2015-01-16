<?php

namespace Brera\Product;

/**
 * @covers Brera\Product\CatalogImportDomainEvent
 */
class CatalogImportDomainEventTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldReturnCatalogImportXml()
	{
		$xml = '<?xml version="1.0"?><rootNode></rootNode>';

		$domainEvent = new CatalogImportDomainEvent($xml);
		$result = $domainEvent->getXml($xml);

		$this->assertEquals($xml, $result);
	}
}
