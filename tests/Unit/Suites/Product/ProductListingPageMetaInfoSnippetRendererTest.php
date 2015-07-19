<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextBuilder;
use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\SnippetList;
use Brera\SnippetRenderer;
use Brera\Snippet;
use Brera\UrlPathKeyGenerator;

/**
 * @covers \Brera\Product\ProductListingCriteriaSnippetRenderer
 * @uses   \Brera\Product\ProductListingMetaInfoSnippetContent
 * @uses   \Brera\Snippet
 */
class ProductListingCriteriaSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $dummyUrlKey = 'foo';

    /**
     * @var SnippetList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetList;

    /**
     * @var ProductListingCriteriaSnippetRenderer
     */
    private $renderer;

    protected function setUp()
    {
        $stubContext = $this->getMock(Context::class);

        $mockUrlPathKeyGenerator = $this->getMock(UrlPathKeyGenerator::class);
        $mockUrlPathKeyGenerator->method('getUrlKeyForPathInContext')->willReturn($this->dummyUrlKey);

        $mockContextBuilder = $this->getMock(ContextBuilder::class, [], [], '', false);
        $mockContextBuilder->method('getContext')->willReturn($stubContext);

        $this->mockSnippetList = $this->getMock(SnippetList::class);

        $this->renderer = new ProductListingCriteriaSnippetRenderer(
            $this->mockSnippetList,
            $mockUrlPathKeyGenerator,
            $mockContextBuilder
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testSnippetWithValidJsonAsContentInAListIsReturned()
    {
        $mockProductListingSource = $this->getMockProductListingSource();
        $expectedSnippet = $this->getExpectedSnippet();

        $this->mockSnippetList->expects($this->once())->method('add')->with($expectedSnippet);

        $this->renderer->render($mockProductListingSource);
    }

    /**
     * @return ProductListingSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockProductListingSource()
    {
        $mockSearchCriteria = $this->getMock(SearchCriteria::class, [], [], '', false);
        $mockProductListingSource = $this->getMock(ProductListingSource::class, [], [], '', false);
        $mockProductListingSource->method('getContextData')->willReturn([]);
        $mockProductListingSource->method('getCriteria')->willReturn($mockSearchCriteria);

        return $mockProductListingSource;
    }

    /**
     * @return Snippet
     */
    private function getExpectedSnippet()
    {
        return Snippet::create(
            ProductListingSnippetRenderer::CODE . '_' . $this->dummyUrlKey,
            json_encode([
                'product_selection_criteria' => null,
                'root_snippet_code'          => ProductListingSnippetRenderer::CODE,
                'page_snippet_codes'         => [ProductListingSnippetRenderer::CODE]
            ])
        );
    }
}
