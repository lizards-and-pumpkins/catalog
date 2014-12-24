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
	 * @expectedException \Brera\PoC\Product\ProductAttributeNotFoundException
	 */
	public function itShouldThrownAnExceptionIfBlankCodeIsProvided()
	{
		$this->attributeList->getAttribute('');
	}

	/**
	 * @test
	 * @expectedException \Brera\PoC\Product\ProductAttributeNotFoundException
	 */
	public function itShouldThrownAnExceptionIfNoAttributeWithGivenCodeIsSet()
	{
		$this->attributeList->getAttribute('foo');
	}

	/**
	 * @test
	 */
	public function itShouldCreateAttributeListFromXmlNodeList()
	{
		$document = new \DOMDocument();
		$element = $document->createElement('foo', 'bar');
		$element->setAttribute('code', 'name');
		$document->appendChild($element);
		$nodeList = $document->getElementsByTagName('foo');

		$attributeList = ProductAttributeList::fromDomNodeList($nodeList);
		$attributeValue = $attributeList->getAttribute('name');

		$this->assertEquals('bar', $attributeValue);
	}
}
