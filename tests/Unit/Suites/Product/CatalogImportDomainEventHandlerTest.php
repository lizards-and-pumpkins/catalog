<?php

namespace Brera\Product;

use Brera\Queue\Queue;

/**
 * @covers \Brera\Product\CatalogImportDomainEventHandler
 * @uses \Brera\Product\ProductImportDomainEvent
 * @uses \Brera\DomDocumentXPathParser
 */
class CatalogImportDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldEmitProductImportDomainEvents()
	{
		$xml = file_get_contents('product.xml', FILE_USE_INCLUDE_PATH);

		$stubCatalogImportDomainEvent = $this->getMockBuilder(CatalogImportDomainEvent::class)
			->disableOriginalConstructor()
			->getMock();
		$stubCatalogImportDomainEvent->expects($this->once())
			->method('getXml')
			->willReturn($xml);

		$stubEventQueue = $this->getMock(Queue::class);
		$stubEventQueue->expects($this->atLeastOnce())
			->method('add');

		$catalogImportDomainEvent = new CatalogImportDomainEventHandler($stubCatalogImportDomainEvent, $stubEventQueue);
		$catalogImportDomainEvent->process();
	}
}
