<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingTitleSnippetRenderer
 * @uses   \LizardsAndPumpkins\Snippet
 */
class ProductListingTitleSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    private $testSnippetKey = ProductListingTitleSnippetRenderer::CODE . '_foo';

    /**
     * @var ProductListingTitleSnippetRenderer
     */
    private $renderer;

    /**
     * @var ProductListing|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListing;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingTitleSnippetKeyGenerator;

    /**
     * @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextBuilder;

    protected function setUp()
    {
        $this->stubProductListingTitleSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubProductListingTitleSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($this->testSnippetKey);
        $this->stubContextBuilder = $this->getMock(ContextBuilder::class);
        $this->stubContextBuilder->method('createContext')->willReturn($this->getMock(Context::class));
        $this->renderer = new ProductListingTitleSnippetRenderer(
            $this->stubProductListingTitleSnippetKeyGenerator,
            $this->stubContextBuilder
        );
        $this->stubProductListing = $this->getMock(ProductListing::class, [], [], '', false);
        $this->stubProductListing->method('getContextData')->willReturn([]);
    }
    
    public function testItImplementsTheSnippetRendererInterface()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testEmptyArrayIsReturnedIfProductListingHasNoTitleAttribute()
    {
        $this->stubProductListing->method('hasAttribute')
            ->with(ProductListingTitleSnippetRenderer::TITLE_ATTRIBUTE_CODE)->willReturn(false);

        $this->assertSame([], $this->renderer->render($this->stubProductListing));
    }

    public function testItReturnsAProductListingTitleSnippet()
    {
        $testTitle = 'foo';

        $this->stubProductListing->method('hasAttribute')
            ->with(ProductListingTitleSnippetRenderer::TITLE_ATTRIBUTE_CODE)->willReturn(true);
        $this->stubProductListing->method('getAttributeValueByCode')
            ->with(ProductListingTitleSnippetRenderer::TITLE_ATTRIBUTE_CODE)->willReturn($testTitle);

        $result = $this->renderer->render($this->stubProductListing);
        
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(Snippet::class, $result);
        $this->assertSame($this->testSnippetKey, $result[0]->getKey());
        $this->assertSame($testTitle, $result[0]->getContent());
    }
}
