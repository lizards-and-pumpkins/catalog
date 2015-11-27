<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Product\ProductTaxClassSnippetRenderer
 * @uses   \LizardsAndPumpkins\Snippet
 */
class ProductTaxClassSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductTaxClassSnippetRenderer
     */
    private $snippetRenderer;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProduct;

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetKeyGenerator;

    protected function setUp()
    {
        $this->mockSnippetList = $this->getMock(SnippetList::class);
        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        
        $this->snippetRenderer = new ProductTaxClassSnippetRenderer(
            $this->mockSnippetList,
            $this->mockSnippetKeyGenerator
        );
        $this->stubProduct = $this->getMock(Product::class);
    }
    
    public function testItIsASnippetRenderer()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->snippetRenderer);
    }

    public function testItAddsASnippetWithTheProductTaxClass()
    {
        $dummyTaxClass = 'test';
        $dummyTaxClassSnippetKey = 'test-key';
        $stubContext = $this->getMock(Context::class);

        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $mockProduct */
        $mockProduct = $this->getMock(Product::class);
        $mockProduct->method('getTaxClass')->willReturn($dummyTaxClass);
        $mockProduct->method('getContext')->willReturn($stubContext);

        $this->mockSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($dummyTaxClassSnippetKey);

        $expectedSnippet = Snippet::create($dummyTaxClassSnippetKey, $dummyTaxClass);

        $this->mockSnippetList->expects($this->once())
            ->method('add')
            ->with($expectedSnippet);

        $this->snippetRenderer->render($mockProduct);
    }
}
