<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductAttributeList
 * @uses \Brera\Product\ProductAttribute
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
	public function itShouldAddAndGetAttributeFromAProductAttributeList()
	{
		$attributeArray = [
			'nodeName'      => 'foo',
			'attributes'    => [],
			'value'         => 'bar'
		];

		$attribute = ProductAttribute::fromArray($attributeArray);

		$this->attributeList->add($attribute);
		$result = $this->attributeList->getAttribute('foo');

		$this->assertEquals('bar', $result->getValue());
	}

	/**
	 * @test
	 * @expectedException \Brera\Product\ProductAttributeNotFoundException
	 */
	public function itShouldThrownAnExceptionIfBlankCodeIsProvided()
	{
		$this->attributeList->getAttribute('');
	}

	/**
	 * @test
	 * @expectedException \Brera\Product\ProductAttributeNotFoundException
	 */
	public function itShouldThrownAnExceptionIfNoAttributeWithGivenCodeIsSet()
	{
		$this->attributeList->getAttribute('foo');
	}

	/**
	 * @test
	 */
	public function itShouldCreateAttributeListFromAttributesArray()
	{
		$attributeArray = [[
			'nodeName'      => 'foo',
			'attributes'    => [],
			'value'         => 'bar'
		]];

		$attributeList = ProductAttributeList::fromArray($attributeArray);
		$attribute = $attributeList->getAttribute('foo');

		$this->assertEquals('bar', $attribute->getValue());
	}
}
