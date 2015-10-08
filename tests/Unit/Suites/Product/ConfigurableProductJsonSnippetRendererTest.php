<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
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
    /**
     * @var ConfigurableProductJsonSnippetRenderer
     */
    private $renderer;

    /**
     * @var ConfigurableProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubConfigurableProduct;

    /**
     * @var AssociatedProductList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubAssociatedProductList;

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
        
        $this->stubConfigurableProduct = $this->getMock(ConfigurableProduct::class, [], [], '', false);
        $this->stubConfigurableProduct->method('getContext')->willReturn($this->getMock(Context::class));
        
        $this->stubAssociatedProductList = $this->getMock(AssociatedProductList::class, [], [], '', false);
        $this->stubConfigurableProduct->method('getAssociatedProducts')->willReturn($this->stubAssociatedProductList);
    }

    public function testItReturnsAnEmptySnippetListIfTheProductIsNotConfigurable()
    {
        $stubOtherProductType = $this->getMock(Product::class, [], [], '', false);
        $snippetList = $this->renderer->render($stubOtherProductType);
        $this->assertCount(0, $snippetList);
    }

    public function testItRendersTwoSnippetsForAConfigurableProduct()
    {
        $snippetList = $this->renderer->render($this->stubConfigurableProduct);
        $this->assertCount(2, $snippetList);
    }

    public function testItAddsTheVariationAttributesJsonSnippetToTheResultingSnippetList()
    {
        $snippetList = $this->renderer->render($this->stubConfigurableProduct);
        $snippet = $this->getSnippetWithKey($this->testVariationAttributesSnippetKey, $snippetList);
        $this->assertInstanceOf(Snippet::class, $snippet);
    }

    public function testItReturnsTheAssociatedProductsWithoutThePhpClassNames()
    {
        $this->stubAssociatedProductList->method('jsonSerialize')->willReturn([
            AssociatedProductList::PHP_CLASSES_KEY => 'foo',
            'products' => ['a', 'b', 'c']
        ]);
        $snippetList = $this->renderer->render($this->stubConfigurableProduct);
        $snippet = $this->getSnippetWithKey($this->testAssociatedProductsSnippetKey, $snippetList);
        $this->assertSame(['a', 'b', 'c'], json_decode($snippet->getContent(), true));
    }
}
