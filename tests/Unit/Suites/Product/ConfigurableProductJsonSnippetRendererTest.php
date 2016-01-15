<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Projection\Catalog\CompositeProductView;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;

/**
 * @covers \LizardsAndPumpkins\Product\ConfigurableProductJsonSnippetRenderer
 * @uses   \LizardsAndPumpkins\Snippet
 */
class ConfigurableProductJsonSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    private $testVariationAttributesSnippetKey = 'variations';
    private $testAssociatedProductsSnippetKey = 'associated_products';
    private $testVariationAttributesJsonData = ['variations'];
    private $testAssociatedAttributesJsonData = ['children'];

    /**
     * @var ConfigurableProductJsonSnippetRenderer
     */
    private $renderer;

    /**
     * @var CompositeProductView|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubCompositeProductView;

    /**
     * @param string $snippetKey
     * @param Snippet[] $snippets
     * @return Snippet
     */
    private function getSnippetWithKey($snippetKey, Snippet ...$snippets)
    {
        foreach ($snippets as $snippet) {
            if ($snippet->getKey() === $snippetKey) {
                return $snippet;
            }
        }

        $this->fail(sprintf('No snippet with key "%s" found in snippet list', $snippetKey));
    }

    /**
     * @param string $expected
     * @param Snippet $snippet
     */
    private function assertSnippetContent($expected, Snippet $snippet)
    {
        $this->assertSame($expected, $snippet->getContent());
    }

    protected function setUp()
    {
        $stubVariationAttributesJsonSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $stubVariationAttributesJsonSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($this->testVariationAttributesSnippetKey);

        $stubAssociatedProductsJsonSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $stubAssociatedProductsJsonSnippetKeyGenerator->method('getKeyForContext')
            ->willReturn($this->testAssociatedProductsSnippetKey);
        
        $this->renderer = new ConfigurableProductJsonSnippetRenderer(
            $stubVariationAttributesJsonSnippetKeyGenerator,
            $stubAssociatedProductsJsonSnippetKeyGenerator
        );

        $this->stubCompositeProductView = $this->getMock(CompositeProductView::class, [], [], '', false);
        $this->stubCompositeProductView->method('getContext')->willReturn($this->getMock(Context::class));

        $stubAssociatedProductList = $this->getMock(AssociatedProductList::class, [], [], '', false);
        $stubAssociatedProductList->method('jsonSerialize')->willReturn($this->testAssociatedAttributesJsonData);
        $this->stubCompositeProductView->method('getAssociatedProducts')->willReturn($stubAssociatedProductList);

        $stubVariationAttributes = $this->getMock(ProductVariationAttributeList::class, [], [], '', false);
        $stubVariationAttributes->method('jsonSerialize')->willReturn($this->testVariationAttributesJsonData);
        $this->stubCompositeProductView->method('getVariationAttributes')->willReturn($stubVariationAttributes);
    }

    public function testItReturnsAnEmptyVariationAttributesJsonArraySnippetForNonCompositeProducts()
    {
        /** @var ProductView|\PHPUnit_Framework_MockObject_MockObject $stubNonCompositeProduct */
        $stubNonCompositeProduct = $this->getMock(ProductView::class);
        $stubNonCompositeProduct->method('getContext')->willReturn($this->getMock(Context::class));

        $snippets = $this->renderer->render($stubNonCompositeProduct);

        $snippet = $this->getSnippetWithKey($this->testVariationAttributesSnippetKey, ...$snippets);
        $this->assertSnippetContent(json_encode([]), $snippet);
    }

    public function testItReturnsAnEmptyAssociatedProductsJsonArraySnippetForNonCompositeProducts()
    {
        /** @var ProductView|\PHPUnit_Framework_MockObject_MockObject $stubNonCompositeProduct */
        $stubNonCompositeProduct = $this->getMock(ProductView::class);
        $stubNonCompositeProduct->method('getContext')->willReturn($this->getMock(Context::class));

        $snippets = $this->renderer->render($stubNonCompositeProduct);

        $snippet = $this->getSnippetWithKey($this->testAssociatedProductsSnippetKey, ...$snippets);
        $this->assertSnippetContent(json_encode([]), $snippet);
    }

    public function testVariationAttributesJsonSnippetIsReturned()
    {
        $snippets = $this->renderer->render($this->stubCompositeProductView);
        $snippet = $this->getSnippetWithKey($this->testVariationAttributesSnippetKey, ...$snippets);

        $this->assertSnippetContent(json_encode($this->testVariationAttributesJsonData), $snippet);
    }

    public function testAssociatedProductsJsonSnippetIsReturned()
    {
        $snippets = $this->renderer->render($this->stubCompositeProductView);
        $snippet = $this->getSnippetWithKey($this->testAssociatedProductsSnippetKey, ...$snippets);

        $this->assertSnippetContent(json_encode($this->testAssociatedAttributesJsonData), $snippet);
    }
}
