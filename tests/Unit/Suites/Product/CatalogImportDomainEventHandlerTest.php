<?php

namespace Brera\PoC\Product;

use Brera\Lib\Queue\Queue;

/**
 * @covers Brera\PoC\Product\CatalogImportDomainEventHandler
 * @uses Brera\PoC\Product\ProductImportDomainEvent
 */
class CatalogImportDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldEmitProductImportDomainEvents()
	{
		$stubCatalogImportDomainEvent = $this->getMockBuilder(CatalogImportDomainEvent::class)
			->disableOriginalConstructor()
			->getMock();
		$stubCatalogImportDomainEvent->expects($this->once())
			->method('getXml');

		$productXmlArray = [
			'<?xml version="1.0?><rootNode>some xml</rootNode>',
			'<?xml version="1.0?><rootNode>some other xml</rootNode>'
		];
		$stubProductBuilder = $this->getMock(ProductBuilder::class, ['getProductXmlArray']);
		$stubProductBuilder->expects($this->once())
			->method('getProductXmlArray')
			->willReturn($productXmlArray);

		$stubEventQueue = $this->getMock(Queue::class);
		$stubEventQueue->expects($this->exactly(count($productXmlArray)))
			->method('add');

		$catalogImportDomainEvent = new CatalogImportDomainEventHandler(
			$stubCatalogImportDomainEvent,
			$stubProductBuilder,
			$stubEventQueue
		);
		$catalogImportDomainEvent->process();
	}
}
