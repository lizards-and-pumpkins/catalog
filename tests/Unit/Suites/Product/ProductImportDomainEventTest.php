<?php

namespace Brera\PoC\Product;

/**
 * @covers \Brera\PoC\Product\ProductImportDomainEvent
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

	/**
	 * @test
	 */
	public function itShouldReturnSerializedXml()
	{
		$xml = '<?xml version="1.0"?><rootNode></rootNode>';
		$serializedXml = serialize($xml);

		$domainEvent = new ProductImportDomainEvent($xml);
		$result = $domainEvent->serialize();

		$this->assertSame($serializedXml, $result);
	}

	/**
	 * @test
	 */
	public function itShouldUnserializeSerializedXmlAndSetItBackOnDomainEvent()
	{
		$xml = '<?xml version="1.0"?><rootNode></rootNode>';

		$domainEvent = new ProductImportDomainEvent($xml);
		$serializedDomainEvent = $domainEvent->serialize();

		$newDomainEvent = new ProductImportDomainEvent('');
		$newDomainEvent->unserialize($serializedDomainEvent);

		$this->assertSame($xml, $newDomainEvent->getXml());
	}
}
