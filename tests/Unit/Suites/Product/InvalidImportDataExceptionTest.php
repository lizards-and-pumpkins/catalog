<?php

namespace Brera\PoC\Product;

/**
 * @covers \Brera\PoC\Product\InvalidImportDataException
 */
class InvalidImportDataExceptionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldExtendOutOfBoundsException()
	{
		$exception = new InvalidImportDataException();
		$this->assertInstanceOf(\OutOfBoundsException::class, $exception);
	}
}
