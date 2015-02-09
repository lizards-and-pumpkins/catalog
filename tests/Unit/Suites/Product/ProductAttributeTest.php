<?php

namespace Brera\Product;

use Brera\Environment\Environment;

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

	/**
	 * @param $attributeEnvironment
	 * @return ProductAttribute
	 */
	private function createProductAttributeWithArray(array $attributeEnvironment)
	{
		return ProductAttribute::fromArray([
			'attributes' => $attributeEnvironment,
			'value' => 'dummy-test-value'
		]);
	}

	/**
	 * @param $returnValueMap
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	private function getEnvironmentMockWithReturnValueMap(array $returnValueMap)
	{
		$stubEnvironment = $this->getMock(Environment::class);
		$stubEnvironment->expects($this->any())
			->method('getSupportedCodes')
			->willReturn(array_column($returnValueMap, 0));
		$stubEnvironment->expects($this->any())
			->method('getValue')
			->willReturnMap($returnValueMap);
		return $stubEnvironment;
	}

	/**
	 * @test
	 */
	public function itShouldReturnAnIntegerMatchScore()
	{
		$attribute = $this->createProductAttributeWithArray(['code' => 'name']);
		$stubEnvironment = $this->getEnvironmentMockWithReturnValueMap([]);
		$this->assertInternalType('int', $attribute->getMatchScoreForEnvironment($stubEnvironment));
	}

	/**
	 * @test
	 */
	public function itShouldReturn1ForTheMatchScoreForAnEnvironmentWith1Match()
	{
		$testWebsiteCode = 'foo';
		$attribute = $this->createProductAttributeWithArray([
			'code' => 'name', 'website' => $testWebsiteCode, 'language' => 'bar'
		]);
		$stubEnvironment = $this->getEnvironmentMockWithReturnValueMap([
			['website', $testWebsiteCode],
			['version', '1'],
		]);
		$this->assertSame(1, $attribute->getMatchScoreForEnvironment($stubEnvironment));
	}

	/**
	 * @test
	 */
	public function itShouldReturn2ForTheMatchScoreForAnEnvironmentWith2Matches()
	{
		$testWebsiteCode = 'foo';
		$testLanguageCode = 'bar';
		$attribute = $this->createProductAttributeWithArray([
			'code' => 'name', 'website' => $testWebsiteCode, 'language' => $testLanguageCode
		]);
		$stubEnvironment = $this->getEnvironmentMockWithReturnValueMap([
			['website', $testWebsiteCode],
			['language', $testLanguageCode],
			['version', '1'],
		]);
		$this->assertSame(2, $attribute->getMatchScoreForEnvironment($stubEnvironment));
	}

	/**
	 * @test
	 */
	public function itShouldReturn0ForTheMatchScoreForAnEnvironmentWithNoMatches()
	{
		$attribute = $this->createProductAttributeWithArray([
			'code' => 'name', 'website' => 'foo', 'language' => 'bar'
		]);
		$stubEnvironment = $this->getEnvironmentMockWithReturnValueMap([
			['website', 'buz'],
			['language', 'qux'],
			['version', '1'],
		]);
		$this->assertSame(0, $attribute->getMatchScoreForEnvironment($stubEnvironment));
	}

	/**
	 * @test
	 */
	public function itShouldReturn1ForTheMatchScoreForAnEnvironmentWith1MatchAnd1Miss()
	{
		$testLanguageCode = 'bar';
		$attribute = $this->createProductAttributeWithArray([
			'code' => 'name', 'website' => 'foo', 'language' => $testLanguageCode
		]);
		$stubEnvironment = $this->getEnvironmentMockWithReturnValueMap([
			['website', 'buz'],
			['language', $testLanguageCode],
			['version', '1'],
		]);
		$this->assertSame(1, $attribute->getMatchScoreForEnvironment($stubEnvironment));
	}
}
