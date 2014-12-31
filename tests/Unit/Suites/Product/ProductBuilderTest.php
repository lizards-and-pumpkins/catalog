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
	 * @var ProductBuilder
	 */
	private $builder;

	protected function setUp()
	{
		$this->builder = new ProductBuilder();
	}

	/**
	 * @test
	 */
	public function itShouldCreateAProductFromXml()
	{
		$xml = file_get_contents('product.xml', FILE_USE_INCLUDE_PATH);
		$domDocument = new \DOMDocument();
		$domDocument->loadXML($xml);
		$firstNode = $domDocument->getElementsByTagName('product')->item(0);
		$firstNodeXml = $domDocument->saveXML($firstNode);

		$product = $this->builder->createProductFromXml($firstNodeXml);

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

	/**
	 * @test
	 */
	public function itShouldReturnAnArray()
	{
		$xml = file_get_contents('product.xml', FILE_USE_INCLUDE_PATH);
		$result = $this->builder->getProductXmlArray($xml);

		$this->assertTrue(is_array($result));
	}
}
