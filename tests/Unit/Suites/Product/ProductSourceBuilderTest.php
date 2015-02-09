<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductSourceBuilder
 * @uses \Brera\Product\ProductSource
 * @uses \Brera\Product\ProductId
 * @uses \Brera\Product\PoCSku
 * @uses \Brera\XPathParser
 * @uses \Brera\Product\ProductAttribute
 * @uses \Brera\Product\ProductAttributeList
 */
class ProductSourceBuilderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var ProductSourceBuilder
	 */
	private $builder;

	protected function setUp()
	{
		$this->builder = new ProductSourceBuilder();
	}

	/**
	 * @test
	 */
	public function itShouldCreateAProductFromXml()
	{
		$xml = file_get_contents(__DIR__ . '/../../../shared-fixture/product.xml');
		$domDocument = new \DOMDocument();
		$domDocument->loadXML($xml);
		$firstNode = $domDocument->getElementsByTagName('product')->item(0);
		$firstNodeXml = $domDocument->saveXML($firstNode);

		$product = $this->builder->createProductSourceFromXml($firstNodeXml);

		$this->assertInstanceOf(ProductSource::class, $product);
		$this->assertEquals('118235-251', $product->getId());
		$this->assertEquals('11.95', $product->getAttributeValue('special_price'));
	}

	/**
	 * @test
	 * @expectedException \Brera\Product\InvalidNumberOfSkusPerImportedProductException
	 * @expectedExceptionMessage There must be exactly one SKU in the imported product XML
	 */
	public function itShouldThrowAnExceptionInCaseOfXmlHasNoEssentialData()
	{
		$xml = '<?xml version="1.0"?><node />';
		(new ProductSourceBuilder())->createProductSourceFromXml($xml);
	}
}
