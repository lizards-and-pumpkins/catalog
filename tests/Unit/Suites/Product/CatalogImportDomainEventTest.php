<?php

namespace Brera\PoC\Product;

/**
 * @covers Brera\PoC\Product\CatalogImportDomainEvent
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

	/**
	 * @test
	 */
	public function itShouldReturnSerializedXml()
	{
		$xml = '<?xml version="1.0"?><rootNode></rootNode>';
		$serializedXml = serialize($xml);

		$domainEvent = new CatalogImportDomainEvent($xml);
		$result = $domainEvent->serialize();

		$this->assertEquals($serializedXml, $result);
	}

	/**
	 * @test
	 */
	public function itShouldUnserializeSerializedXmlAndSetItBackOnDomainEvent()
	{
		$xml = '<?xml version="1.0"?><rootNode></rootNode>';

		$domainEvent = new CatalogImportDomainEvent($xml);
		$serializedDomainEvent = $domainEvent->serialize();

		$newDomainEvent = new CatalogImportDomainEvent('');
		$newDomainEvent->unserialize($serializedDomainEvent);

		$this->assertSame($xml, $newDomainEvent->getXml());
	}
}
