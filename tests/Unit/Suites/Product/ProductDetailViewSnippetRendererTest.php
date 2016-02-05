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

    private $testContentSnippetKey = 'stub-content-key';

    private $testMetaSnippetKey = 'stub-meta-key';

    private $testTitleSnippetKey = 'title';

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
     * @param string $key
     * @param Snippet[] $snippets
     * @return Snippet|null
     */
    private function getSnippetWithGivenKey($key, Snippet ...$snippets)
    {
        foreach ($snippets as $snippet) {
            if ($snippet->getKey() === $key) {
                return $snippet;
            }
        }

        return null;
    }

    /**
     * @param string $expectedKey
     * @param Snippet[] $snippets
     */
    private function assertContainsSnippetWithGivenKey($expectedKey, Snippet ...$snippets)
    {
        if ($this->getSnippetWithGivenKey($expectedKey, ...$snippets) !== null) {
            $this->assertTrue(true);
            return;
        }

        $this->fail(sprintf('Failed asserting snippet list contains snippet with "%s" key.', $expectedKey));
    }

    /**
     * @param Snippet[] $snippets
     * @return Snippet
     */
    protected function getProductTitleSnippet(Snippet ...$snippets)
    {
        $titleSnippet = $this->getSnippetWithGivenKey($this->testTitleSnippetKey, ...$snippets);

        if (null === $titleSnippet) {
            $this->fail('Product title snippet has not been rendered.');
        }

        return $titleSnippet;
    }

    protected function prepareSnippetKeyGenerators()
    {
        $this->stubProductDetailViewSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubProductDetailViewSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($this->testContentSnippetKey);

        $this->stubProductTitleSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubProductTitleSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->testTitleSnippetKey);

        $this->stubProductDetailPageMetaSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $this->stubProductDetailPageMetaSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($this->testMetaSnippetKey);
    }

    protected function setUp()
    {
        $blockRenderer = $this->createStubProductDetailViewBlockRenderer();
        $this->prepareSnippetKeyGenerators();

        $this->renderer = new ProductDetailViewSnippetRenderer(
            $blockRenderer,
            $this->stubProductDetailViewSnippetKeyGenerator,
            $this->stubProductTitleSnippetKeyGenerator,
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
        $result = $this->renderer->render($this->stubProductView);

        $this->assertContainsSnippetWithGivenKey($this->testContentSnippetKey, ...$result);
        $this->assertContainsSnippetWithGivenKey($this->testMetaSnippetKey, ...$result);
        $this->assertContainsSnippetWithGivenKey($this->testTitleSnippetKey, ...$result);
    }

    public function testTitleIsNotExceedingDefinedLimit()
    {
        $maxTitleLength = ProductDetailViewSnippetRenderer::MAX_PRODUCT_TITLE_LENGTH;
        $attributeLength = ($maxTitleLength - ProductDetailViewSnippetRenderer::PRODUCT_TITLE_SUFFIX) / 2;
        $this->stubProductView->method('getFirstValueOfAttribute')->willReturn(str_repeat('-', $attributeLength));

        $snippets = $this->renderer->render($this->stubProductView);
        $titleSnippet = $this->getProductTitleSnippet(...$snippets);

        $this->assertLessThanOrEqual($maxTitleLength, $titleSnippet->getContent());
    }

    public function testProductTitleContainsProductTitleSuffix()
    {
        $snippets = $this->renderer->render($this->stubProductView);
        $titleSnippet = $this->getProductTitleSnippet(...$snippets);

        $this->assertContains(ProductDetailViewSnippetRenderer::PRODUCT_TITLE_SUFFIX, $titleSnippet->getContent());
    }

    /**
     * @dataProvider requiredAttributeCodeProvider
     * @param string $requiredAttributeCode
     */
    public function testProductTitleContainsRequiredAttributes($requiredAttributeCode)
    {
        $testAttributeValue = 'foo';

        $this->stubProductView->method('getFirstValueOfAttribute')->willReturnCallback(
            function ($attributeCode) use ($testAttributeValue, $requiredAttributeCode) {
                if ($attributeCode === $requiredAttributeCode) {
                    return $testAttributeValue;
                }

                return '';
            }
        );

        $snippets = $this->renderer->render($this->stubProductView);
        $titleSnippet = $this->getProductTitleSnippet(...$snippets);

        $this->assertContains($testAttributeValue, $titleSnippet->getContent());
    }

    /**
     * @return array[]
     */
    public function requiredAttributeCodeProvider()
    {
        return [
            ['name'],
            ['product_group'],
            ['brand'],
            ['style'],
        ];
    }
}
