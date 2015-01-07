<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\PoCSku
 */
class PoCSkuTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldImplementSkuInterface()
	{
		$sku = PoCSku::fromString('sku-string');

		$this->assertInstanceOf(Sku::class, $sku);
	}

	/**
	 * @test
	 */
	public function itShouldConvertSkuIntoString()
	{
		$skuString = 'sku-string';
		$sku = PoCSku::fromString($skuString);

		$this->assertSame($skuString, (string)$sku);
	}

	/**
	 * @test
	 * @expectedException \Brera\Product\InvalidSkuException
	 * @dataProvider invalidSkuProvider
	 * @param $invalidSku
	 */
	public function itShouldThrowAnExceptionIfSkuIsNotValid($invalidSku)
	{
		PoCSku::fromString($invalidSku);
	}

	public function invalidSkuProvider()
	{
		return [
			[null],
			[[]],
			[new \stdClass()],
			[true],
			[false],
			[''],
			['  '],
			["\n"],
			["\t"]
		];
	}
}
