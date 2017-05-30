<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\ProductDetail\TemplateRendering\ProductDetailViewBlockRenderer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductDetail\ProductDetailViewSnippetRenderer
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 * @uses   \LizardsAndPumpkins\Import\SnippetContainer
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailPageMetaInfoSnippetContent
 * @uses \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class ProductDetailViewSnippetRendererTest extends TestCase
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
    private function createStubProductDetailViewBlockRenderer() : ProductDetailViewBlockRenderer
    {
        $blockRenderer = $this->createMock(ProductDetailViewBlockRenderer::class);
        $blockRenderer->method('render')->willReturnCallback(function () {
            return '';
        });
        $blockRenderer->method('getRootSnippetCode')->willReturn('dummy root block code');
        $blockRenderer->method('getNestedSnippetCodes')->willReturn([]);

        return $blockRenderer;
    }

    private function assertContainsSnippetWithGivenKey(string $expectedKey, Snippet ...$snippets)
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
        $this->stubProductDetailViewSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $this->stubProductDetailPageMetaSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);

        $this->renderer = new ProductDetailViewSnippetRenderer(
            $blockRenderer,
            $this->stubProductDetailViewSnippetKeyGenerator,
            $this->stubProductDetailPageMetaSnippetKeyGenerator
        );

        $this->stubProductView = $this->createMock(ProductView::class);
        $this->stubProductView->method('getContext')->willReturn($this->createMock(Context::class));
        $this->stubProductView->method('getProductPageTitle')->willReturn('');
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

        $this->stubProductView->method('getAllValuesOfAttribute')->willReturn([]);
        $result = $this->renderer->render($this->stubProductView);

        $this->assertContainsSnippetWithGivenKey($testContentSnippetKey, ...$result);
        $this->assertContainsSnippetWithGivenKey($testMetaSnippetKey, ...$result);
    }
}
