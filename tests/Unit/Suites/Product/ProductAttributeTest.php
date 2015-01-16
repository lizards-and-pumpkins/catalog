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
			'attributes'    => ['code' => 'name'],
			'value'         => 'foo'
		]);

		$this->assertTrue($attribute->isCodeEqualsTo('name'));
	}

	/**
	 * @test
	 */
	public function itShouldReturnFalseIfAttributeWithGivenCodeDoesNotExist()
	{
		$attribute = ProductAttribute::fromArray([
			'attributes'    => ['code' => 'name'],
			'value'         => 'foo'
		]);

		$this->assertFalse($attribute->isCodeEqualsTo('price'));
	}

	/**
	 * @test
	 */
	public function itShouldReturnAttributeCode()
	{
		$attribute = ProductAttribute::fromArray([
			'attributes'    => ['code' => 'name'],
			'value'         => 'foo'
		]);

		$this->assertEquals('name', $attribute->getCode());
	}

	/**
	 * @test
	 */
	public function itShouldReturnAttributeValue()
	{
		$attribute = ProductAttribute::fromArray([
			'attributes'    => ['code' => 'name'],
			'value'         => 'foo'
		]);

		$this->assertEquals('foo', $attribute->getValue());
	}

	/**
	 * @test
	 * @expectedException \Brera\FirstCharOfAttributeCodeIsNotAlphabeticException
	 * @dataProvider invalidAttributeCodeProvider
	 * @param $invalidAttributeCode
	 */
	public function itShouldThrowAnExceptionIfAttributeCodeStartWithNonAlphabeticCharacter($invalidAttributeCode)
	{
		ProductAttribute::fromArray([
			'attributes'    => ['code' => $invalidAttributeCode],
			'value'         => 'foo'
		]);
	}

	public function invalidAttributeCodeProvider()
	{
		return [
			[null],
			[''],
			[' '],
			['1'],
			['-bar'],
			['2foo'],
			["\nbaz"]
		];
	}
}
