<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductAttribute
 * @uses \Brera\Product\ProductAttributeList
 */
class ProductAttributeTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldReturnTrueIfAttributeWithGivenCodeExists()
	{
		$attribute = ProductAttribute::fromArray([
			'nodeName'      => 'foo',
			'attributes'    => [],
			'value'         => 'bar'
		]);

		$this->assertTrue($attribute->isCodeEqualsTo('foo'));
	}

	/**
	 * @test
	 */
	public function itShouldReturnFalseIfAttributeWithGivenCodeDoesNotExist()
	{
		$attribute = ProductAttribute::fromArray([
			'nodeName'      => 'foo',
			'attributes'    => [],
			'value'         => 'bar'
		]);

		$this->assertFalse($attribute->isCodeEqualsTo('baz'));
	}

	/**
	 * @test
	 */
	public function itShouldReturnAttributeCode()
	{
		$attribute = ProductAttribute::fromArray([
			'nodeName'      => 'foo',
			'attributes'    => [],
			'value'         => 'bar'
		]);

		$this->assertEquals('foo', $attribute->getCode());
	}

	/**
	 * @test
	 */
	public function itShouldReturnAttributeValue()
	{
		$attribute = ProductAttribute::fromArray([
			'nodeName'      => 'foo',
			'attributes'    => [],
			'value'         => 'bar'
		]);

		$this->assertEquals('bar', $attribute->getValue());
	}

	/**
	 * @test
	 */
	public function itShouldReturnAttributeWithSubAttribute()
	{
		$attribute = ProductAttribute::fromArray([
			'nodeName'      => 'foo',
			'attributes'    => [],
			'value'         => [
				[
					'nodeName'      => 'bar',
					'attributes'    => [],
					'value'         => 1
				],
				[
					'nodeName'      => 'baz',
					'attributes'    => [],
					'value'         => 2
				]
			]
		]);

		/** @var ProductAttributeList $attributeValue */
		$attributeValue = $attribute->getValue();

		$this->assertInstanceOf(ProductAttributeList::class, $attributeValue);
		$this->assertEquals(1, $attributeValue->getAttribute('bar')->getValue());
		$this->assertEquals(2, $attributeValue->getAttribute('baz')->getValue());
	}
}
