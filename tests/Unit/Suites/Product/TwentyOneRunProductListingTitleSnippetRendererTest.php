<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTitleSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\TwentyOneRunProductListingTitleSnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\TwentyOneRunProductListingTitleSnippetRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 */
class TwentyOneRunProductListingTitleSnippetRendererTest extends \PHPUnit_Framework_TestCase
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
        $this->renderer = new TwentyOneRunProductListingTitleSnippetRenderer(
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
        $this->stubProductListing->method('hasAttribute')->with('meta_title')->willReturn(false);
        $this->assertSame([], $this->renderer->render($this->stubProductListing));
    }

    public function testItReturnsAProductListingTitleSnippet()
    {
        $testTitle = 'foo';

        $this->stubProductListing->method('hasAttribute')->with('meta_title')->willReturn(true);
        $this->stubProductListing->method('getAttributeValueByCode')->with('meta_title')->willReturn($testTitle);

        $result = $this->renderer->render($this->stubProductListing);
        $expectedSnippetContents = $testTitle . TwentyOneRunProductListingTitleSnippetRenderer::TITLE_SUFFIX;
        
        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(Snippet::class, $result);
        $this->assertSame($this->testSnippetKey, $result[0]->getKey());
        $this->assertSame($expectedSnippetContents, $result[0]->getContent());
    }
}
