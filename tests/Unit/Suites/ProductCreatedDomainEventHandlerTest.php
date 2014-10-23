<?php

namespace Brera\PoC;

use Brera\PoC\Product\ProductId;
use Brera\PoC\Product\ProductRepository;
use Brera\PoC\Product\Product;

/**
 * @covers \Brera\PoC\ProductCreatedDomainEventHandler
 */
class ProductCreatedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldTriggerAProjection()
	{
		$stubProductId = $this->getMockBuilder(ProductId::class)
			->disableOriginalConstructor()
			->getMock();

		$stubProduct = $this->getMockBuilder(Product::class)
			->disableOriginalConstructor()
			->getMock();

		$stubDomainEvent = $this->getMockBuilder(ProductCreatedDomainEvent::class)
			->disableOriginalConstructor()
			->getMock();
		$stubDomainEvent->expects($this->once())
			->method('getProductId')
			->willReturn($stubProductId);

		$stubProductRepository = $this->getMock(ProductRepository::class);
		$stubProductRepository->expects($this->once())
			->method('findById')
			->willReturn($stubProduct);

		$stubProjector = $this->getMockBuilder(PoCProductProjector::class)
			->disableOriginalConstructor()
			->getMock();
		$stubProjector->expects($this->once())
			->method('project');

		(new ProductCreatedDomainEventHandler($stubDomainEvent, $stubProductRepository, $stubProjector))->process();
	}
}
