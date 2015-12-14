<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\ContextBuilder\ContextCountry;
use LizardsAndPumpkins\Context\ContextBuilder\ContextWebsite;
use LizardsAndPumpkins\Product\Tax\TaxService;
use LizardsAndPumpkins\Product\Tax\TaxServiceLocator;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetList;
use LizardsAndPumpkins\TaxableCountries;

/**
 * @covers \LizardsAndPumpkins\Product\PriceSnippetRenderer
 * @uses   \LizardsAndPumpkins\Product\Price
 * @uses   \LizardsAndPumpkins\Product\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Snippet
 * @uses   \LizardsAndPumpkins\SnippetList
 * @uses   \LizardsAndPumpkins\Website\Website
 * @uses   \LizardsAndPumpkins\Country\Country
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
    private $stubTaxableCountries;

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetKeyGenerator;

    /**
     * @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextBuilder;

    /**
     * @var TaxServiceLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubTaxServiceLocator;

    /**
     * @var string
     */
    private $dummyPriceAttributeCode = 'foo';

    /**
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProduct()
    {
        $stubProduct = $this->getMock(Product::class);
        $stubProduct->method('getContext')->willReturn($this->getMock(Context::class));
        return $stubProduct;
    }

    protected function setUp()
    {
        $stubTaxService = $this->getMock(TaxService::class);
        $stubTaxService->method('applyTo')->willReturnArgument(0);
        $this->stubTaxServiceLocator = $this->getMock(TaxServiceLocator::class);
        $this->stubTaxServiceLocator->method('get')->willReturn($stubTaxService);
        
        $this->stubTaxableCountries = $this->getMock(TaxableCountries::class);
        $this->stubTaxableCountries->method('getIterator')->willReturn(new \ArrayIterator($this->testCountries));
        $this->stubTaxableCountries->method('getCountries')->willReturn($this->testCountries);
        
        $this->stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        
        $this->stubContextBuilder = $this->getMock(ContextBuilder::class);
        $this->stubContextBuilder->method('expandContext')->willReturn($this->getMock(Context::class));

        $this->renderer = new PriceSnippetRenderer(
            $this->stubTaxableCountries,
            $this->stubTaxServiceLocator,
            $this->stubSnippetKeyGenerator,
            $this->stubContextBuilder,
            $this->dummyPriceAttributeCode
        );
    }

    public function testSnippetRendererInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testItReturnsASnippetList()
    {
        $this->assertInstanceOf(SnippetList::class, $this->renderer->render($this->createStubProduct()));
    }

    public function testNothingIsAddedToSnippetListIfProductDoesNotHaveARequiredAttribute()
    {
        $stubProduct = $this->createStubProduct();
        $stubProduct->method('hasAttribute')->with($this->dummyPriceAttributeCode)->willReturn(false);

        $snippetList = $this->renderer->render($stubProduct);
        $this->assertCount(0, $snippetList);
    }

    public function testSnippetListContainingSnippetsWithGivenKeyAndPriceIsReturned()
    {
        $dummyPriceSnippetKey = 'bar';
        $dummyPriceAttributeValue = 1;

        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->createStubProduct();
        $stubProduct->method('hasAttribute')->with($this->dummyPriceAttributeCode)->willReturn(true);
        $stubProduct->method('getFirstValueOfAttribute')
            ->with($this->dummyPriceAttributeCode)
            ->willReturn($dummyPriceAttributeValue);
        $stubProduct->method('getTaxClass')->willReturn('test class');
        $this->stubContextWebsiteAndCountry($stubProduct);

        $this->stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($dummyPriceSnippetKey);

        $snippetList = $this->renderer->render($stubProduct);
        $this->assertCount(2, $snippetList);
    }

    /**
     * @param Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct
     */
    private function stubContextWebsiteAndCountry($stubProduct)
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $stubProduct->getContext();
        $stubContext->method('getValue')->willReturnMap([
            [ContextWebsite::CODE, 'test website'],
            [ContextCountry::CODE, 'XX'],
        ]);
    }
}
