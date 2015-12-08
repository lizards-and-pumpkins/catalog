<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Projection\Catalog\InternalToPublicProductJsonData;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;

/**
 * @covers \LizardsAndPumpkins\Product\ConfigurableProductJsonSnippetRenderer
 * @uses   \LizardsAndPumpkins\SnippetList
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
     * @var CompositeProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubCompositeProduct;

    /**
     * @var InternalToPublicProductJsonData|\PHPUnit_Framework_TestCase
     */
    private $stubInternalToPublicProductJsonData;

    /**
     * @param string $snippetKey
     * @param SnippetList $snippetList
     * @return Snippet
     */
    private function getSnippetWithKey($snippetKey, SnippetList $snippetList)
    {
        /** @var Snippet $snippet */
        foreach ($snippetList as $snippet) {
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
        
        $this->stubInternalToPublicProductJsonData = $this->getMock(InternalToPublicProductJsonData::class);
        $this->stubInternalToPublicProductJsonData->method('transformVariationAttributes')
            ->willReturn($this->testVariationAttributesJsonData);
        $this->stubInternalToPublicProductJsonData->method('transformAssociatedProducts')
            ->willReturn($this->testAssociatedAttributesJsonData);

        $this->renderer = new ConfigurableProductJsonSnippetRenderer(
            $stubVariationAttributesJsonSnippetKeyGenerator,
            $stubAssociatedProductsJsonSnippetKeyGenerator,
            $this->stubInternalToPublicProductJsonData
        );

        $this->stubCompositeProduct = $this->getMock(CompositeProduct::class, [], [], '', false);
        $this->stubCompositeProduct->method('getContext')->willReturn($this->getMock(Context::class));

        $stubAssociatedProductList = $this->getMock(AssociatedProductList::class, [], [], '', false);
        $stubAssociatedProductList->method('jsonSerialize')->willReturn([]);
        $this->stubCompositeProduct->method('getAssociatedProducts')->willReturn($stubAssociatedProductList);

        $stubVariationAttributes = $this->getMock(ProductVariationAttributeList::class, [], [], '', false);
        $stubVariationAttributes->method('jsonSerialize')->willReturn([]);
        $this->stubCompositeProduct->method('getVariationAttributes')->willReturn($stubVariationAttributes);
    }

    public function testItReturnsAnEmptyVariationAttributesJsonArraySnippetForNonCompositeProducts()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubNonCompositeProduct */
        $stubNonCompositeProduct = $this->getMock(Product::class);
        $stubNonCompositeProduct->method('getContext')->willReturn($this->getMock(Context::class));

        $snippetList = $this->renderer->render($stubNonCompositeProduct);

        $snippet = $this->getSnippetWithKey($this->testVariationAttributesSnippetKey, $snippetList);
        $this->assertSnippetContent(json_encode([]), $snippet);
    }

    public function testItReturnsAnEmptyAssociatedProductsJsonArraySnippetForNonCompositeProducts()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubNonConfigurableProduct */
        $stubNonConfigurableProduct = $this->getMock(Product::class);
        $stubNonConfigurableProduct->method('getContext')->willReturn($this->getMock(Context::class));

        $snippetList = $this->renderer->render($stubNonConfigurableProduct);

        $snippet = $this->getSnippetWithKey($this->testAssociatedProductsSnippetKey, $snippetList);
        $this->assertSnippetContent(json_encode([]), $snippet);
    }

    public function testItAddsTheVariationAttributesJsonSnippetToTheResultingSnippetList()
    {
        $snippetList = $this->renderer->render($this->stubCompositeProduct);

        $snippet = $this->getSnippetWithKey($this->testVariationAttributesSnippetKey, $snippetList);

        $this->assertSnippetContent(json_encode($this->testVariationAttributesJsonData), $snippet);
    }

    public function testItAddsTheAssociatedProductsJsonSnippetToTheResultingSnippetList()
    {
        $snippetList = $this->renderer->render($this->stubCompositeProduct);

        $snippet = $this->getSnippetWithKey($this->testAssociatedProductsSnippetKey, $snippetList);

        $this->assertSnippetContent(json_encode($this->testAssociatedAttributesJsonData), $snippet);
    }
}
