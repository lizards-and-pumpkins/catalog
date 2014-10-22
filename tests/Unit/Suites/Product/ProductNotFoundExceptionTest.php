<?php

namespace Brera\PoC\Product;

/**
 * @covers \Brera\PoC\Product\ProductNotFoundException
 */
class ProductNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldExtendOutOfBoundsException()
	{
		$exception = new ProductNotFoundException();
		$this->assertInstanceOf(\OutOfBoundsException::class, $exception);
	}
}
