<?php

namespace Brera\Product;

use Brera\KeyValue\DataPoolReader;

/**
 * @covers \Brera\Product\ProductDetailHtmlPage
 */
class ProductDetailHtmlPageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldGetProductHtmlFromDataPoolReader()
    {
        $stubProductId = $this->getMockBuilder(ProductId::class)
        ->disableOriginalConstructor()
        ->getMock();

        $stubDataPoolReader = $this->getMockBuilder(DataPoolReader::class)
        ->disableOriginalConstructor()
        ->getMock();
        $stubDataPoolReader->expects($this->once())
        ->method('getPoCProductHtml');

        $productDetailHtmlPage = new ProductDetailHtmlPage($stubProductId, $stubDataPoolReader);
        $productDetailHtmlPage->process();
    }
}
