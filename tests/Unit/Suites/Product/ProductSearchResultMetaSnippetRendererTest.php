<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Renderer\BlockRenderer;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetContent
 * @uses   \LizardsAndPumpkins\Snippet
 * @uses   \LizardsAndPumpkins\SnippetList
 */
class ProductSearchResultMetaSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $dummySnippetKey = 'foo';

    /**
     * @var string
     */
    private $dummyRootSnippetCode = 'bar';

    /**
     * @var ProductSearchResultMetaSnippetRenderer
     */
    private $renderer;

    /**
     * @var ProductsPerPageForContextList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductsPerPageForContextList;

    /**
     * @var ContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextSource;

    protected function setUp()
    {
        $testSnippetList = new SnippetList;

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->dummySnippetKey);

        /** @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubBlockRenderer */
        $stubBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);
        $stubBlockRenderer->method('getRootSnippetCode')->willReturn($this->dummyRootSnippetCode);
        $stubBlockRenderer->method('getNestedSnippetCodes')->willReturn([]);

        $stubContext = $this->getMock(Context::class);
        $this->stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->stubContextSource->method('getAllAvailableContexts')->willReturn([$stubContext]);

        $this->renderer = new ProductSearchResultMetaSnippetRenderer(
            $testSnippetList,
            $stubSnippetKeyGenerator,
            $stubBlockRenderer,
            $this->stubContextSource
        );

        $this->stubProductsPerPageForContextList = $this->getMockBuilder(ProductsPerPageForContextList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stubProductsPerPageForContextList->method('getListOfAvailableNumberOfProductsPerPageForContext')
            ->willReturn([9]);
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testSnippetListIsReturned()
    {
        $result = $this->renderer->render($this->stubProductsPerPageForContextList);
        $this->assertInstanceOf(SnippetList::class, $result);
    }

    public function testSnippetWithValidJsonAsContentAddedToList()
    {
        $expectedSnippetContent = [
            ProductSearchResultMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => $this->dummyRootSnippetCode,
            ProductSearchResultMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [$this->dummyRootSnippetCode]
        ];
        $expectedSnippet = Snippet::create($this->dummySnippetKey, json_encode($expectedSnippetContent));

        $result = $this->renderer->render($this->stubProductsPerPageForContextList);

        $this->assertInstanceOf(SnippetList::class, $result);
        $this->assertCount(1, $result);
        $this->assertContainsOnly(Snippet::class, $result);
        $this->assertEquals($expectedSnippet, $result->getIterator()->current());
    }
}
