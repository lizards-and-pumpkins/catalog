<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
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
     * @var ConfigurableProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubConfigurableProduct;

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

        $this->stubConfigurableProduct = $this->getMock(ConfigurableProduct::class, [], [], '', false);
        $this->stubConfigurableProduct->method('getContext')->willReturn($this->getMock(Context::class));

        $stubAssociatedProductList = $this->getMock(AssociatedProductList::class, [], [], '', false);
        $stubAssociatedProductList->method('jsonSerialize')->willReturn([]);
        $this->stubConfigurableProduct->method('getAssociatedProducts')->willReturn($stubAssociatedProductList);

        $stubVariationAttributes = $this->getMock(ProductVariationAttributeList::class, [], [], '', false);
        $stubVariationAttributes->method('jsonSerialize')->willReturn([]);
        $this->stubConfigurableProduct->method('getVariationAttributes')->willReturn($stubVariationAttributes);
    }

    public function testItReturnsAnEmptyVariationAttributesJsonArraySnippetForNonConfigurableProducts()
    {
        $stubNonConfigurableProduct = $this->getMock(Product::class, [], [], '', false);
        $stubNonConfigurableProduct->method('getContext')->willReturn($this->getMock(Context::class));

        $snippetList = $this->renderer->render($stubNonConfigurableProduct);

        $snippet = $this->getSnippetWithKey($this->testVariationAttributesSnippetKey, $snippetList);
        $this->assertSnippetContent(json_encode([]), $snippet);
    }

    public function testItReturnsAnEmptyAssociatedProductsJsonArraySnippetForNonConfigurableProducts()
    {
        $stubNonConfigurableProduct = $this->getMock(Product::class, [], [], '', false);
        $stubNonConfigurableProduct->method('getContext')->willReturn($this->getMock(Context::class));

        $snippetList = $this->renderer->render($stubNonConfigurableProduct);

        $snippet = $this->getSnippetWithKey($this->testAssociatedProductsSnippetKey, $snippetList);
        $this->assertSnippetContent(json_encode([]), $snippet);
    }

    public function testItAddsTheVariationAttributesJsonSnippetToTheResultingSnippetList()
    {
        $snippetList = $this->renderer->render($this->stubConfigurableProduct);

        $snippet = $this->getSnippetWithKey($this->testVariationAttributesSnippetKey, $snippetList);

        $this->assertSnippetContent(json_encode($this->testVariationAttributesJsonData), $snippet);
    }

    public function testItAddsTheAssociatedProductsJsonSnippetToTheResultingSnippetList()
    {
        $snippetList = $this->renderer->render($this->stubConfigurableProduct);

        $snippet = $this->getSnippetWithKey($this->testAssociatedProductsSnippetKey, $snippetList);

        $this->assertSnippetContent(json_encode($this->testAssociatedAttributesJsonData), $snippet);
    }
}
