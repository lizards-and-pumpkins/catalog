<?php

namespace Brera\PoC\Product;

/**
 * @covers \Brera\PoC\Product\ProductApiRequestHandler
 * @covers \Brera\PoC\Api\ApiRequestHandler
 */
class ProductApiRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldCallProtectedMethodOfConcreteClass()
	{
		$productApiRequestHandler = new ProductApiRequestHandler();
		$productApiRequestHandler->setMethod('import');
		$result = $productApiRequestHandler->process();

		$this->assertEquals('"dummy response"', $result);
	}
}
