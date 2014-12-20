<?php

namespace Brera\PoC\Renderer;

use Brera\PoC\Product\Product;

/**
 * @covers \Brera\PoC\Renderer\PoCProductRenderer
 */
class PoCProductRendererTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldReturnAnHtmlString()
	{
		$stubProductId = '123';
		$stubProductName = 'Name';
		$expectation = '<p>' . $stubProductId . ': ' . $stubProductName . '</p>';

		$stubProduct = $this->getMockBuilder(Product::class)
			->disableOriginalConstructor()
			->getMock();

		$stubProduct->expects($this->once())
			->method('getId')
			->willReturn($stubProductId);
		$stubProduct->expects($this->once())
			->method('getAttributeValue')
			->with('name')
			->willReturn($stubProductName);

		$renderer = new PoCProductRenderer();
		$result = $renderer->render($stubProduct);

		$this->assertEquals($expectation, $result);
	}
}
