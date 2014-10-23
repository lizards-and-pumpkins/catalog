<?php

namespace Brera\PoC;

use Brera\PoC\Product\ProductId;

/**
 * @covers \Brera\PoC\ProductCreatedDomainEvent
 */
class ProductCreatedDomainEventTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldReturnProductId()
	{
		$stubProductId = $this->getMockBuilder(ProductId::class)
		                      ->disableOriginalConstructor()
		                      ->getMock();

		$domainEvent = new ProductCreatedDomainEvent($stubProductId);
		$result = $domainEvent->getProductId();

		$this->assertEquals($stubProductId, $result);
	}
}
