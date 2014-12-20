<?php

namespace Brera\PoC\Product;

/**
 * @covers \Brera\PoC\Product\ProductAttributeList
 * @uses \Brera\PoC\Product\ProductAttribute
 */
class ProductAttributeListTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var ProductAttributeList
	 */
	private $attributeList;

	protected function setUp()
	{
		$this->attributeList = new ProductAttributeList();
	}

	/**
	 * @test
	 */
	public function itShouldAddAndGetAttributeFromAList()
	{
		$document = new \DOMDocument();
		$domElement = $document->createElement('attribute', 'bar');
		$domElement->setAttribute('code', 'foo');

		$attribute = ProductAttribute::fromDomElement($domElement);

		$this->attributeList->add($attribute);
		$value = $this->attributeList->getAttribute('foo');

		$this->assertEquals('bar', $value);
	}

	/**
	 * @test
	 */
	public function itShouldReturnNullIfBlankCodeIsProvided()
	{
		$value = $this->attributeList->getAttribute('');

		$this->assertNull($value);
	}

	/**
	 * @test
	 */
	public function itShouldReturnNullIfNoAttributeWithGivenCodeIsSet()
	{
		$value = $this->attributeList->getAttribute('foo');

		$this->assertNull($value);
	}
}
