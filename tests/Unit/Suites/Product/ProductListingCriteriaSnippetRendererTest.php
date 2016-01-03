<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;
use LizardsAndPumpkins\Snippet;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingCriteriaSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductListingCriteriaSnippetContent
 * @uses   \LizardsAndPumpkins\Snippet
 * @uses   \LizardsAndPumpkins\SnippetList
 */
class ProductListingCriteriaSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetKeyGenerator;

    /**
     * @var ProductListingCriteriaSnippetRenderer
     */
    private $renderer;

    /**
     * @return ProductListingCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProductListingCriteria()
    {
        $stubSearchCriteria = $this->getMock(CompositeSearchCriterion::class, [], [], '', false);
        $stubProductListingCriteria = $this->getMock(ProductListingCriteria::class, [], [], '', false);
        $stubProductListingCriteria->method('getContextData')->willReturn([]);
        $stubProductListingCriteria->method('getCriteria')->willReturn($stubSearchCriteria);

        return $stubProductListingCriteria;
    }

    protected function setUp()
    {
        /** @var ProductListingBlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubListingBlockRenderer */
        $stubListingBlockRenderer = $this->getMock(ProductListingBlockRenderer::class, [], [], '', false);
        $stubListingBlockRenderer->method('render')->willReturn('dummy content');
        $stubListingBlockRenderer->method('getRootSnippetCode')->willReturn('dummy root block code');
        $stubListingBlockRenderer->method('getNestedSnippetCodes')->willReturn([]);

        $this->stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        /** @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject $stubContextBuilder */
        $stubContextBuilder = $this->getMock(ContextBuilder::class);
        $stubContextBuilder->method('createContext')->willReturn($this->getMock(Context::class));

        $this->renderer = new ProductListingCriteriaSnippetRenderer(
            $stubListingBlockRenderer,
            $this->stubSnippetKeyGenerator,
            $stubContextBuilder
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testSnippetWithValidJsonAsContentInAListIsReturned()
    {
        $testSnippetKey = 'foo';
        $this->stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($testSnippetKey);

        $stubProductListingCriteria = $this->createStubProductListingCriteria();
        $result = $this->renderer->render($stubProductListingCriteria);

        $expectedSnippetContents = json_encode([
            'product_selection_criteria' => null,
            'root_snippet_code' => 'product_listing',
            'page_snippet_codes' => ['product_listing']
        ]);

        $expectedSnippet = Snippet::create($testSnippetKey, $expectedSnippetContents);

        $this->assertInstanceOf(SnippetList::class, $result);
        $this->assertEquals([$expectedSnippet], iterator_to_array($result));
    }
}
