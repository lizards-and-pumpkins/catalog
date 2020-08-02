<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Price;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\Country\Country;
use LizardsAndPumpkins\Context\Website\Website;
use LizardsAndPumpkins\Import\Exception\InvalidDataObjectTypeException;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;
use LizardsAndPumpkins\Import\Tax\TaxService;
use LizardsAndPumpkins\Import\Tax\TaxServiceLocator;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\Import\Tax\TaxableCountries;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Price\PriceSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\Price\Price
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\DataPool\KeyValueStore\Snippet
 * @uses   \LizardsAndPumpkins\Context\Website\Website
 * @uses   \LizardsAndPumpkins\Context\Country\Country
 */
class PriceSnippetRendererTest extends TestCase
{
    private $testCountries = ['DE', 'UK'];

    /**
     * @var PriceSnippetRenderer
     */
    private $renderer;

    /**
     * @var TaxableCountries|MockObject
     */
    private $stubTaxableCountries;

    /**
     * @var SnippetKeyGenerator|MockObject
     */
    private $stubSnippetKeyGenerator;

    /**
     * @var ContextBuilder|MockObject
     */
    private $stubContextBuilder;

    /**
     * @var TaxServiceLocator|MockObject
     */
    private $stubTaxServiceLocator;

    /**
     * @var AttributeCode
     */
    private $testPriceAttributeCode;

    /**
     * @return ProductView|MockObject
     */
    private function createStubProductView(): ProductView
    {
        $stubProduct = $this->createMock(Product::class);
        $stubProduct->method('getContext')->willReturn($this->createMock(Context::class));

        $stubProductView = $this->createMock(ProductView::class);
        $stubProductView->method('getOriginalProduct')->willReturn($stubProduct);

        return $stubProductView;
    }

    /**
     * @param Product|MockObject $stubProduct
     */
    private function stubContextWebsiteAndCountry($stubProduct): void
    {
        /** @var Context|MockObject $stubContext */
        $stubContext = $stubProduct->getContext();
        $stubContext->method('getValue')->willReturnMap([
            [Website::CONTEXT_CODE, 'test website'],
            [Country::CONTEXT_CODE, 'XX'],
        ]);
    }

    final protected function setUp(): void
    {
        $stubTaxService = $this->createMock(TaxService::class);
        $stubTaxService->method('applyTo')->willReturnArgument(0);
        $this->stubTaxServiceLocator = $this->createMock(TaxServiceLocator::class);
        $this->stubTaxServiceLocator->method('get')->willReturn($stubTaxService);

        $this->stubTaxableCountries = $this->createMock(TaxableCountries::class);
        $this->stubTaxableCountries->method('getIterator')->willReturn(new \ArrayIterator($this->testCountries));
        $this->stubTaxableCountries->method('getCountries')->willReturn($this->testCountries);

        $this->stubSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);

        $this->stubContextBuilder = $this->createMock(ContextBuilder::class);
        $this->stubContextBuilder->method('expandContext')->willReturn($this->createMock(Context::class));

        $this->testPriceAttributeCode = AttributeCode::fromString('foo');

        $this->renderer = new PriceSnippetRenderer(
            $this->stubTaxableCountries,
            $this->stubTaxServiceLocator,
            $this->stubSnippetKeyGenerator,
            $this->stubContextBuilder,
            $this->testPriceAttributeCode
        );
    }

    public function testIsSnippetRenderer(): void
    {
        $this->assertInstanceOf(SnippetRenderer::class, $this->renderer);
    }

    public function testReturnsSnippets(): void
    {
        $result = $this->renderer->render($this->createStubProductView());
        $this->assertContainsOnly(Snippet::class, $result);
    }

    public function testThrowsExceptionIfDataObjectIsNotProductView(): void
    {
        $this->expectException(InvalidDataObjectTypeException::class);
        $this->expectExceptionMessage('Data object must be ProductView, got string.');

        $this->renderer->render('foo');
    }

    public function testReturnsEmptyArrayIfProductDoesNotHaveARequiredAttribute(): void
    {
        $stubProduct = $this->createStubProductView();
        $stubProduct->method('hasAttribute')->with($this->testPriceAttributeCode)->willReturn(false);

        $result = $this->renderer->render($stubProduct);
        $this->assertCount(0, $result);
    }

    public function testReturnsSnippetsWithGivenKeyAndPrice(): void
    {
        $dummyPriceSnippetKey = 'bar';
        $dummyPriceAttributeValue = 1;

        $stubProduct = $this->createMock(Product::class);
        $stubProduct->method('getContext')->willReturn($this->createMock(Context::class));
        $stubProduct->method('hasAttribute')->with($this->testPriceAttributeCode)->willReturn(true);
        $stubProduct->method('getFirstValueOfAttribute')->with($this->testPriceAttributeCode)
            ->willReturn($dummyPriceAttributeValue);
        $stubProduct->method('getTaxClass')->willReturn(ProductTaxClass::fromString('test class'));
        $this->stubContextWebsiteAndCountry($stubProduct);

        /** @var ProductView|MockObject $stubProductView */
        $stubProductView = $this->createMock(ProductView::class);
        $stubProductView->method('getOriginalProduct')->willReturn($stubProduct);

        $this->stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($dummyPriceSnippetKey);

        $result = $this->renderer->render($stubProductView);
        $this->assertCount(2, $result);
    }
}
