<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextBuilder;
use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;
use Brera\Snippet;

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
    private $dummySnippetKey = 'foo';

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

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $mockSnippetKeyGenerator */
        $mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $mockSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->dummySnippetKey);

        /** @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject $mockContextBuilder */
        $mockContextBuilder = $this->getMock(ContextBuilder::class, [], [], '', false);
        $mockContextBuilder->method('getContext')->willReturn($stubContext);

        $this->mockSnippetList = $this->getMock(SnippetList::class);

        $this->renderer = new ProductListingCriteriaSnippetRenderer(
            $this->mockSnippetList,
            $mockSnippetKeyGenerator,
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
        return Snippet::create($this->dummySnippetKey, json_encode([
            'product_selection_criteria' => null,
            'root_snippet_code'          => ProductListingSnippetRenderer::CODE,
            'page_snippet_codes'         => [ProductListingSnippetRenderer::CODE]
        ]));
    }
}
