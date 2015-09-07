<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextBuilder;
use Brera\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;
use Brera\Snippet;

/**
 * @covers \Brera\Product\ProductListingMetaInfoSnippetRenderer
 * @uses   \Brera\Product\ProductListingMetaInfoSnippetContent
 * @uses   \Brera\Snippet
 */
class ProductListingMetaInfoSnippetRendererTest extends \PHPUnit_Framework_TestCase
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
     * @var ProductListingMetaInfoSnippetRenderer
     */
    private $renderer;

    /**
     * @return ProductListingMetaInfoSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockProductListingMetaInfoSource()
    {
        $mockSearchCriteria = $this->getMock(CompositeSearchCriterion::class, [], [], '', false);
        $mockProductListingMetaInfoSource = $this->getMock(ProductListingMetaInfoSource::class, [], [], '', false);
        $mockProductListingMetaInfoSource->method('getContextData')->willReturn([]);
        $mockProductListingMetaInfoSource->method('getCriteria')->willReturn($mockSearchCriteria);

        return $mockProductListingMetaInfoSource;
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

    protected function setUp()
    {
        $stubContext = $this->getMock(Context::class);

        /**
         * @var ProductListingBlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubProductListingBlockRenderer
         */
        $stubProductListingBlockRenderer = $this->getMock(ProductListingBlockRenderer::class, [], [], '', false);
        $stubProductListingBlockRenderer->method('render')->willReturn('dummy content');
        $stubProductListingBlockRenderer->method('getRootSnippetCode')->willReturn('dummy root block code');
        $stubProductListingBlockRenderer->method('getNestedSnippetCodes')->willReturn([]);

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $mockSnippetKeyGenerator */
        $mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $mockSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->dummySnippetKey);

        /** @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject $mockContextBuilder */
        $mockContextBuilder = $this->getMock(ContextBuilder::class, [], [], '', false);
        $mockContextBuilder->method('createContext')->willReturn($stubContext);

        $this->mockSnippetList = $this->getMock(SnippetList::class);

        $this->renderer = new ProductListingMetaInfoSnippetRenderer(
            $this->mockSnippetList,
            $stubProductListingBlockRenderer,
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
        $mockProductListingMetaInfoSource = $this->getMockProductListingMetaInfoSource();
        $expectedSnippet = $this->getExpectedSnippet();

        $this->mockSnippetList->expects($this->once())->method('add')->with($expectedSnippet);

        $this->renderer->render($mockProductListingMetaInfoSource);
    }
}
