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
        
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(Product::class);
        $stubProduct->method('getTaxClass')->willReturn($dummyTaxClass);
        $stubProduct->method('getContext')->willReturn($this->getMock(Context::class));

        $this->mockSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($dummyTaxClassSnippetKey);

        $expectedSnippet = Snippet::create($dummyTaxClassSnippetKey, $dummyTaxClass);

        $this->mockSnippetList->expects($this->once())
            ->method('add')
            ->with($expectedSnippet);

        $this->snippetRenderer->render($stubProduct);
    }

    public function testItReturnsTheSnippetList()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(Product::class);
        $stubProduct->method('getTaxClass')->willReturn('test');
        $stubProduct->method('getContext')->willReturn($this->getMock(Context::class));

        $this->mockSnippetKeyGenerator->method('getKeyForContext')->willReturn('foo');
        
        $this->assertSame($this->mockSnippetList, $this->snippetRenderer->render($stubProduct));
    }
}
