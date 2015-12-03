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

    /**
     * @return ProductListingCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockProductListingCriteria()
    {
        $mockSearchCriteria = $this->getMock(CompositeSearchCriterion::class, [], [], '', false);
        $mockProductListingCriteria = $this->getMock(ProductListingCriteria::class, [], [], '', false);
        $mockProductListingCriteria->method('getContextData')->willReturn([]);
        $mockProductListingCriteria->method('getCriteria')->willReturn($mockSearchCriteria);

        return $mockProductListingCriteria;
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
        $mockContextBuilder = $this->getMock(ContextBuilder::class);
        $mockContextBuilder->method('createContext')->willReturn($stubContext);

        $this->mockSnippetList = $this->getMock(SnippetList::class, [], [], '', false);

        $this->renderer = new ProductListingCriteriaSnippetRenderer(
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
        $mockProductListingCriteria = $this->getMockProductListingCriteria();

        $this->mockSnippetList->expects($this->once())->method('add')->with($this->isInstanceOf(Snippet::class));

        $this->renderer->render($mockProductListingCriteria);
    }
}
