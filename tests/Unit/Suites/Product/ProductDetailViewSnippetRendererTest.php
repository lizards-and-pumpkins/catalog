<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Product\ProductDetailViewSnippetRenderer
 * @uses   \LizardsAndPumpkins\Snippet
 * @uses   \LizardsAndPumpkins\Product\ProductDetailPageMetaInfoSnippetContent
 */
class ProductDetailViewSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductDetailViewSnippetRenderer
     */
    private $renderer;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductDetailViewSnippetKeyGenerator;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductDetailPageMetaSnippetKeyGenerator;

    /**
     * @var ProductView|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductView;

    /**
     * @return ProductDetailViewBlockRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProductDetailViewBlockRenderer()
    {
        $blockRenderer = $this->getMock(ProductDetailViewBlockRenderer::class, [], [], '', false);
        $blockRenderer->method('render')->willReturn('dummy content');
        $blockRenderer->method('getRootSnippetCode')->willReturn('dummy root block code');
        $blockRenderer->method('getNestedSnippetCodes')->willReturn([]);

        return $blockRenderer;
    }

    /**
     * @param string $expectedKey
     * @param Snippet[] $snippets
     */
    private function assertContainsSnippetWithGivenKey($expectedKey, Snippet ...$snippets)
    {
        foreach ($snippets as $snippet) {
            if ($snippet->getKey() === $expectedKey) {
                $this->assertTrue(true);
                return;
            }
        }

        $this->fail(sprintf('Failed asserting snippet list contains snippet with "%s" key.', $expectedKey));
    }

    protected function setUp()
    {
        $blockRenderer = $this->createStubProductDetailViewBlockRenderer();
        $this->stubProductDetailViewSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubProductDetailPageMetaSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        $this->renderer = new ProductDetailViewSnippetRenderer(
            $blockRenderer,
            $this->stubProductDetailViewSnippetKeyGenerator,
            $this->stubProductDetailPageMetaSnippetKeyGenerator
        );

        $this->stubProductView = $this->getMock(ProductView::class);
        $this->stubProductView->method('getContext')->willReturn($this->getMock(Context::class));
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testProductDetailViewSnippetsAreRendered()
    {
        $testContentSnippetKey = 'stub-content-key';
        $testMetaSnippetKey = 'stub-meta-key';

        $this->stubProductDetailViewSnippetKeyGenerator->method('getKeyForContext')->willReturn($testContentSnippetKey);
        $this->stubProductDetailPageMetaSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($testMetaSnippetKey);

        $result = $this->renderer->render($this->stubProductView);

        $this->assertContainsSnippetWithGivenKey($testContentSnippetKey, ...$result);
        $this->assertContainsSnippetWithGivenKey($testMetaSnippetKey, ...$result);
    }

    public function testContextPartsFetchingIsDelegatedToKeyGenerator()
    {
        $dummyContextParts = ['foo', 'bar', 'baz'];
        $this->stubProductDetailViewSnippetKeyGenerator->method('getContextPartsUsedForKey')
            ->willReturn($dummyContextParts);

        $this->assertSame($dummyContextParts, $this->renderer->getUsedContextParts());
    }
}
