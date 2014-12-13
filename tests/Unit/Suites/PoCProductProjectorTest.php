<?php

namespace Brera\PoC;

use Brera\PoC\Product\ProductId;
use Brera\PoC\Renderer\ProductRenderer;
use Brera\PoC\KeyValue\DataPoolWriter;
use Brera\PoC\Product\Product;

/**
 * @covers \Brera\PoC\PoCProductProjector
 */
class PoCProductProjectorTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldSetProductHtmlOnDataPoolWriter()
	{
		$stubProductRenderer = $this->getMock(ProductRenderer::class);
		$stubProductRenderer->expects($this->atLeastOnce())
			->method('render');

		$stubProductId = $this->getMockBuilder(ProductId::class)
			->disableOriginalConstructor()
			->getMock();

		$stubDataPoolWriter = $this->getMockBuilder(DataPoolWriter::class)
			->disableOriginalConstructor()
			->getMock();
		$stubDataPoolWriter->expects($this->atLeastOnce())
			->method('setPoCProductHtml');

		$stubProduct = $this->getMockBuilder(Product::class)
			->disableOriginalConstructor()
			->getMock();
		$stubProduct->expects($this->once())
			->method('getId')
			->willReturn($stubProductId);

		$projector = new PoCProductProjector(array($stubProductRenderer), $stubDataPoolWriter);
		$projector->project($stubProduct);
	}
}
