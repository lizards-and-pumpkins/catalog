<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\TaxableCountries;

/**
 * @covers \LizardsAndPumpkins\Product\PriceSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\Price
 * @uses   \LizardsAndPumpkins\Snippet
 */
class PriceSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    private $testCountries = ['DE', 'UK'];

    /**
     * @var PriceSnippetRenderer
     */
    private $renderer;

    /**
     * @var TaxableCountries|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockTaxableCountries;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetKeyGenerator;

    /**
     * @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockContextBuilder;

    /**
     * @var string
     */
    private $dummyPriceAttributeCode = 'foo';

    /**
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockProduct()
    {
        $stubProduct = $this->getMock(Product::class);
        $stubProduct->method('getContext')->willReturn($this->getMock(Context::class));
        return $stubProduct;
    }

    protected function setUp()
    {
        $this->mockTaxableCountries = $this->getMock(TaxableCountries::class);
        $this->mockTaxableCountries->method('getIterator')->willReturn(new \ArrayIterator($this->testCountries));
        $this->mockTaxableCountries->method('getCountries')->willReturn($this->testCountries);
        
        $this->mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        
        $this->mockContextBuilder = $this->getMock(ContextBuilder::class);
        $this->mockContextBuilder->method('expandContext')->willReturn($this->getMock(Context::class));

        $this->renderer = new PriceSnippetRenderer(
            $this->mockTaxableCountries,
            $this->mockSnippetKeyGenerator,
            $this->mockContextBuilder,
            $this->dummyPriceAttributeCode
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testItReturnsASnippetList()
    {
        $this->assertInstanceOf(SnippetList::class, $this->renderer->render($this->createMockProduct()));
    }

    public function testNothingIsAddedToSnippetListIfProductDoesNotHaveARequiredAttribute()
    {
        $stubProduct = $this->createMockProduct();
        $stubProduct->method('hasAttribute')->with($this->dummyPriceAttributeCode)->willReturn(false);

        $snippetList = $this->renderer->render($stubProduct);
        $this->assertCount(0, $snippetList);
    }

    public function testSnippetListContainingSnippetsWithGivenKeyAndPriceIsReturned()
    {
        $dummyPriceSnippetKey = 'bar';
        $dummyPriceAttributeValue = 1;

        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->createMockProduct();
        $stubProduct->method('hasAttribute')->with($this->dummyPriceAttributeCode)->willReturn(true);
        $stubProduct->method('getFirstValueOfAttribute')
            ->with($this->dummyPriceAttributeCode)
            ->willReturn($dummyPriceAttributeValue);

        $this->mockSnippetKeyGenerator->method('getKeyForContext')->willReturn($dummyPriceSnippetKey);

        $snippetList = $this->renderer->render($stubProduct);
        $this->assertCount(2, $snippetList);
    }
}
