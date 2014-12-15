<?php

namespace Brera\PoC\Product;

/**
 * @covers \Brera\PoC\Product\ProductBuilder
 * @uses \Brera\PoC\Product\Product
 * @uses \Brera\PoC\Product\ProductId
 * @uses \Brera\PoC\Product\PoCSku
 */
class ProductBuilderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldCreateAProductFromXml()
	{
		$xml = file_get_contents('../../doc/example-simple-product.xml');

		$builder = new ProductBuilder();
		$product = $builder->createProductFromXml($xml);

		$this->assertInstanceOf(Product::class, $product);
	}

	/**
	 * @test
	 * @expectedException \Brera\PoC\Product\InvalidImportDataException
	 */
	public function itShouldThrowAnExceptionInCaseOfInvalidXml()
	{
		$xml = 'not a valid XML string';
		(new ProductBuilder())->createProductFromXml($xml);
	}

	/**
	 * @test
	 * @expectedException \Brera\PoC\Product\InvalidImportDataException
	 */
	public function itShouldThrowAnExceptionInCaseOfXmlHasNoEssentialData()
	{
		$xml = '<?xml version="1.0"?><node />';
		(new ProductBuilder())->createProductFromXml($xml);
	}
}
