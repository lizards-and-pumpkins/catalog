<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Renderer\BlockRenderer;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Product\ProductSearchAutosuggestionMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\ProductSearchAutosuggestionMetaSnippetContent
 * @uses   \LizardsAndPumpkins\Snippet
 */
class ProductSearchAutosuggestionMetaSnippetRendererTest extends \PHPUnit_Framework_TestCase
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
     * @var ProductSearchAutosuggestionMetaSnippetRenderer
     */
    private $renderer;

    /**
     * @var ContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextSource;

    protected function setUp()
    {
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

        $this->renderer = new ProductSearchAutosuggestionMetaSnippetRenderer(
            $stubSnippetKeyGenerator,
            $stubBlockRenderer,
            $this->stubContextSource
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testArrayOfSnippetsIsReturned()
    {
        $result = $this->renderer->render('dummy-data-object');
        $this->assertContainsOnly(Snippet::class, $result);
    }

    public function testSnippetWithValidJsonAsContentAddedToList()
    {
        $expectedSnippetContent = [
            ProductSearchAutosuggestionMetaSnippetContent::KEY_ROOT_SNIPPET_CODE  => $this->dummyRootSnippetCode,
            ProductSearchAutosuggestionMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [$this->dummyRootSnippetCode],
            ProductSearchAutosuggestionMetaSnippetContent::KEY_CONTAINER_SNIPPETS => []
        ];
        $expectedSnippet = Snippet::create($this->dummySnippetKey, json_encode($expectedSnippetContent));

        $result = $this->renderer->render('dummy-data-object');

        $this->assertEquals([$expectedSnippet], $result);
    }
}
