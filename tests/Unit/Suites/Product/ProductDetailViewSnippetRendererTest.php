<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;

/**
 * @covers \LizardsAndPumpkins\Product\ProductDetailViewSnippetRenderer
 * @uses   \LizardsAndPumpkins\Snippet
 * @uses   \LizardsAndPumpkins\SnippetContainer
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
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductTitleSnippetKeyGenerator;

    /**
     * @var ProductView|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductView;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductDetailPageMetaDescriptionSnippetKeyGenerator;

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

    /**
     * @param string $snippetKey
     * @param Snippet[] $result
     * @return Snippet
     */
    private function findSnippetByKey($snippetKey, array $result)
    {
        return array_reduce($result, function ($found, Snippet $snippet) use ($snippetKey) {
            if ($found) {
                return $found;
            }
            return $snippet->getKey() === $snippetKey ?
                $snippet :
                null;
        });
    }

    /**
     * @param Snippet $metaSnippet
     * @param string $containerCode
     * @param string $expectedSnippetCode
     */
    private function assertContainerContainsSnippet(Snippet $metaSnippet, $containerCode, $expectedSnippetCode)
    {
        $pageData = json_decode($metaSnippet->getContent(), true);

        $this->assertArrayHasKey(
            $containerCode,
            $pageData[PageMetaInfoSnippetContent::KEY_CONTAINER_SNIPPETS],
            sprintf('Container %s does not exist.', $containerCode)
        );

        $this->assertContains(
            $expectedSnippetCode,
            $pageData[PageMetaInfoSnippetContent::KEY_CONTAINER_SNIPPETS][$containerCode],
            sprintf('Container %s does not contain a snippet with key %s', $containerCode, $expectedSnippetCode)
        );
    }

    protected function setUp()
    {
        $blockRenderer = $this->createStubProductDetailViewBlockRenderer();
        $this->stubProductDetailViewSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubProductTitleSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubProductDetailPageMetaSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubProductDetailPageMetaDescriptionSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        $this->renderer = new ProductDetailViewSnippetRenderer(
            $blockRenderer,
            $this->stubProductDetailViewSnippetKeyGenerator,
            $this->stubProductTitleSnippetKeyGenerator,
            $this->stubProductDetailPageMetaSnippetKeyGenerator,
            $this->stubProductDetailPageMetaDescriptionSnippetKeyGenerator
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
        $testTitleSnippetKey = 'title';
        $testMetaDescriptionSnippetKey = 'meta-description';

        $this->stubProductDetailViewSnippetKeyGenerator->method('getKeyForContext')->willReturn($testContentSnippetKey);
        $this->stubProductTitleSnippetKeyGenerator->method('getKeyForContext')->willReturn($testTitleSnippetKey);
        $this->stubProductDetailPageMetaSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($testMetaSnippetKey);
        $this->stubProductDetailPageMetaDescriptionSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($testMetaDescriptionSnippetKey);

        $result = $this->renderer->render($this->stubProductView);

        $this->assertContainsSnippetWithGivenKey($testContentSnippetKey, ...$result);
        $this->assertContainsSnippetWithGivenKey($testMetaSnippetKey, ...$result);
        $this->assertContainsSnippetWithGivenKey($testTitleSnippetKey, ...$result);
        $this->assertContainsSnippetWithGivenKey($testMetaDescriptionSnippetKey, ...$result);

        $metaSnippet = array_reduce($result, function ($carry, Snippet $item) use ($testMetaSnippetKey) {
            return $item->getKey() === $testMetaSnippetKey ? $item : $carry;
        });

        $expectedSnippetCode = ProductDetailViewSnippetRenderer::META_DESCRIPTION_CODE;
        $this->assertContainerContainsSnippet($metaSnippet, 'head_container', $expectedSnippetCode);
    }

    public function testContainerSnippetsAreAssigned()
    {
        $testMetaSnippetKey = 'stub-meta-key';

        $this->stubProductDetailViewSnippetKeyGenerator->method('getKeyForContext')->willReturn('foo');
        $this->stubProductTitleSnippetKeyGenerator->method('getKeyForContext')->willReturn('bar');
        $this->stubProductDetailPageMetaDescriptionSnippetKeyGenerator->method('getKeyForContext')->willReturn('buz');
        $this->stubProductDetailPageMetaSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($testMetaSnippetKey);

        $result = $this->renderer->render($this->stubProductView);
        
        $metaSnippet = $this->findSnippetByKey($testMetaSnippetKey, $result);
        $this->assertContainerContainsSnippet($metaSnippet, 'title', ProductDetailViewSnippetRenderer::TITLE_KEY_CODE);
        $this->assertContainerContainsSnippet($metaSnippet, 'head_container', ProductCanonicalTagSnippetRenderer::CODE);
    }
}
