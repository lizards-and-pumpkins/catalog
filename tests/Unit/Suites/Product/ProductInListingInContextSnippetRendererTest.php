<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;

/**
 * @covers \Brera\Product\ProductInListingInContextSnippetRenderer
 * @uses   \Brera\Snippet
 */
class ProductInListingInContextSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    private $testProductId = 2;
    
    /**
     * @var ProductInListingInContextSnippetRenderer
     */
    private $renderer;

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetKeyGenerator;

    /**
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubProduct()
    {
        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubProduct->method('getId')
            ->willReturn($this->testProductId);
        return $stubProduct;
    }

    protected function setUp()
    {
        $this->mockSnippetList = $this->getMock(SnippetList::class);

        $stubProductInListingBlockRenderer = $this->getMock(ProductInListingBlockRenderer::class, [], [], '', false );
        $stubProductInListingBlockRenderer->method('render')->willReturn('dummy content');
        $stubProductInListingBlockRenderer->method('getRootSnippetCode')->willReturn('dummy root block code');
        $stubProductInListingBlockRenderer->method('getNestedSnippetCodes')->willReturn([]);

        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        $this->renderer = new ProductInListingInContextSnippetRenderer(
            $this->mockSnippetList,
            $stubProductInListingBlockRenderer,
            $this->mockSnippetKeyGenerator
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testProductInListingViewSnippetsAreRendered()
    {
        $this->mockSnippetKeyGenerator->method('getKeyForContext')->willReturn('stub-content-key');
        
        $this->mockSnippetList->expects($this->once())->method('add');

        $stubProduct = $this->getStubProduct();
        $stubContext = $this->getMock(Context::class, [], [], '', false);

        $this->renderer->render($stubProduct, $stubContext);
    }

    public function testProductIdIsPassedToKeyGenerator()
    {
        $stubContext = $this->getMock(Context::class);
        $stubProduct = $this->getStubProduct();

        $this->mockSnippetKeyGenerator->method('getKeyForContext')->willReturn('stub-content-key');
        $this->mockSnippetKeyGenerator->expects($this->once())->method('getKeyForContext')
            ->with($this->anything(), ['product_id' => $this->testProductId]);
        $this->renderer->render($stubProduct, $stubContext);
    }
}
