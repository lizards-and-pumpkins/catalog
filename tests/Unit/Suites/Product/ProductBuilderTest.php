<?php

namespace Brera\PoC\Product;

/**
 * @covers \Brera\PoC\Product\ProductBuilder
 * @uses \Brera\PoC\Product\Product
 * @uses \Brera\PoC\Product\ProductId
 * @uses \Brera\PoC\Product\PoCSku
 * @uses \Brera\PoC\PoCDomParser
 * @uses \Brera\PoC\Product\ProductAttribute
 * @uses \Brera\PoC\Product\ProductAttributeList
 */
class ProductBuilderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldCreateAProductFromXml()
	{
//		die(__DIR__);
		$xml = file_get_contents('example-simple-product.xml', FILE_USE_INCLUDE_PATH);

		$builder = new ProductBuilder();
		$product = $builder->createProductFromXml($xml);

		$this->assertInstanceOf(Product::class, $product);
	}

	/**
	 * @test
	 * @expectedException \Brera\PoC\Product\InvalidNumberOfSkusPerImportedProductException
	 */
	public function itShouldThrowAnExceptionInCaseOfXmlHasNoEssentialData()
	{
		$xml = '<?xml version="1.0"?><node />';
		(new ProductBuilder())->createProductFromXml($xml);
	}
}
