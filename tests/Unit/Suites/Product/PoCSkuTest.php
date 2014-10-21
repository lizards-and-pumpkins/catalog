<?php

namespace Brera\PoC\Product;

/**
 * @covers \Brera\PoC\Product\PoCSku
 */
class PoCSkuTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldImplementSkuInterface()
	{
		$sku = new PoCSku('sku-string');
		$this->assertInstanceOf(Sku::class, $sku);
	}

	/**
	 * @test
	 */
	public function itShouldConvertSkuIntoString(){
		$skuString = 'sku-string';
		$sku = new PoCSku($skuString);
		$this->assertSame($skuString, (string) $sku);
	}
}
