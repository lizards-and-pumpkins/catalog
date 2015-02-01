<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductAttribute
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
}
