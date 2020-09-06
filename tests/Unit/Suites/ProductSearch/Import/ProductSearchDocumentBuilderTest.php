<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\Import\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Import\Price\Price;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Tax\TaxService;
use LizardsAndPumpkins\Import\Tax\TaxServiceLocator;
use LizardsAndPumpkins\Import\Tax\TaxableCountries;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\Import\ProductSearchDocumentBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\DefaultAttributeValueCollector
 * @uses   \LizardsAndPumpkins\Import\Price\Price
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 */
class ProductSearchDocumentBuilderTest extends TestCase
{
    private $dummyTaxableCountries = ['DE', 'UK'];

    private $dummyPriceInclTax = '12199';

    /**
     * @var AttributeValueCollectorLocator
     */
    private $stubValueCollectorLocator;

    /**
     * @var TaxableCountries
     */
    private $stubTaxableCountries;

    /**
     * @var TaxServiceLocator
     */
    private $stubTaxServiceLocator;

    /**
     * @param array[] $attributesMap
     * @return Product
     */
    private function createStubProduct(array $attributesMap) : Product
    {
        $stubProductId = $this->createMock(ProductId::class);
        $stubProductId->method('__toString')->willReturn('test-id');
        $stubProduct = $this->createMock(Product::class);
        $stubProduct->method('getAllValuesOfAttribute')->willReturnMap($attributesMap);
        $stubProduct->method('getContext')->willReturn($this->createMock(Context::class));
        $stubProduct->method('getId')->willReturn($stubProductId);
        $stubProduct->method('hasAttribute')
            ->willReturnCallback(function (AttributeCode $attributeCode) use ($attributesMap) {
                foreach ($attributesMap as $attributeMap) {
                    if ($attributeCode->isEqualTo($attributeMap[0])) {
                        return true;
                    }
                }

                return false;
            });

        return $stubProduct;
    }

    /**
     * @param SearchDocument $document
     * @param string $attributeCode
     * @param mixed[] $attributeValues
     */
    private function assertDocumentContainsField(
        SearchDocument $document,
        string $attributeCode,
        array $attributeValues
    ) {
        $searchDocumentField = SearchDocumentField::fromKeyAndValues($attributeCode, $attributeValues);
        $this->assertTrue(in_array($searchDocumentField, $document->getFieldsCollection()->getFields()));
    }

    private function createInstance(string ...$searchableAttributes) : ProductSearchDocumentBuilder
    {
        return new ProductSearchDocumentBuilder(
            $searchableAttributes,
            $this->stubValueCollectorLocator,
            $this->stubTaxableCountries,
            $this->stubTaxServiceLocator
        );
    }

    final protected function setUp(): void
    {
        $this->stubTaxableCountries = $this->createMock(TaxableCountries::class);
        $this->stubTaxableCountries->method('getCountries')->willReturn($this->dummyTaxableCountries);
        $stubTaxService = $this->createMock(TaxService::class);
        $stubTaxService->method('applyTo')->willReturn(Price::fromFractions($this->dummyPriceInclTax));
        $this->stubTaxServiceLocator = $this->createMock(TaxServiceLocator::class);
        $this->stubTaxServiceLocator->method('get')->willReturn($stubTaxService);
        $this->stubValueCollectorLocator = $this->createMock(AttributeValueCollectorLocator::class);
        $this->stubValueCollectorLocator->method('forProduct')
            ->willReturn(new DefaultAttributeValueCollector());
    }

    public function testSearchDocumentBuilderInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(SearchDocumentBuilder::class, $this->createInstance());
    }

    public function testExceptionIsThrownIfProjectionSourceDataIsNotProduct(): void
    {
        $this->expectException(InvalidProjectionSourceDataTypeException::class);
        $this->createInstance()->aggregate('invalid-projection-source-data');
    }

    public function testSearchDocumentContainingIndexedAttributeIsReturned(): void
    {
        $searchableAttribute = 'foo';
        $attributeValues = ['bar'];

        $attributesMap = [[$searchableAttribute, $attributeValues]];
        $stubProduct = $this->createStubProduct($attributesMap);

        $searchDocumentBuilder = $this->createInstance($searchableAttribute);
        $result = $searchDocumentBuilder->aggregate($stubProduct);

        $this->assertInstanceOf(SearchDocument::class, $result);
        $this->assertDocumentContainsField($result, $searchableAttribute, $attributeValues);
    }

    public function testProductPriceIsIndexedIfProductHasNoSpecialPrice(): void
    {
        $priceAttributeCode = PriceSnippetRenderer::PRICE;
        $priceValues = ['1000'];

        $attributesMap = [[$priceAttributeCode, $priceValues]];
        $stubProduct = $this->createStubProduct($attributesMap);

        $searchDocumentBuilder = $this->createInstance($priceAttributeCode);
        $result = $searchDocumentBuilder->aggregate($stubProduct);

        $this->assertInstanceOf(SearchDocument::class, $result);
        $this->assertDocumentContainsField($result, $priceAttributeCode, $priceValues);
    }

    public function testProductSpecialPriceIsIndexedAsPriceIfProductHasSpecialPrice(): void
    {
        $priceAttributeCode = PriceSnippetRenderer::PRICE;
        $priceValues = ['1000'];

        $specialPriceAttributeCode = PriceSnippetRenderer::SPECIAL_PRICE;
        $specialPriceValues = ['900'];

        $attributesMap = [[$priceAttributeCode, $priceValues], [$specialPriceAttributeCode, $specialPriceValues]];
        $stubProduct = $this->createStubProduct($attributesMap);

        $searchDocumentBuilder = $this->createInstance($priceAttributeCode);
        $result = $searchDocumentBuilder->aggregate($stubProduct);

        $this->assertInstanceOf(SearchDocument::class, $result);
        $this->assertDocumentContainsField($result, $priceAttributeCode, $specialPriceValues);
    }

    public function testItIncludesTheProductIdInTheSearchDocumentFields(): void
    {
        $searchableAttribute = 'foo';
        $attributeValues = ['bar'];

        $attributesMap = [[$searchableAttribute, $attributeValues]];
        $stubProduct = $this->createStubProduct($attributesMap);

        $searchDocumentBuilder = $this->createInstance($searchableAttribute);
        $result = $searchDocumentBuilder->aggregate($stubProduct);

        $this->assertInstanceOf(SearchDocument::class, $result);
        $this->assertDocumentContainsField($result, 'product_id', [(string) $stubProduct->getId()]);
    }

    public function testItAddsThePriceIncludingTaxForEachTaxableCountry(): void
    {
        $priceField = 'price';
        $priceExcludingTax = ['100'];

        $attributesMap = [[$priceField, $priceExcludingTax]];
        $stubProduct = $this->createStubProduct($attributesMap);

        $searchDocumentBuilder = $this->createInstance($priceField);
        $result = $searchDocumentBuilder->aggregate($stubProduct);

        foreach ($this->dummyTaxableCountries as $countryCode) {
            $priceWithTaxField = 'price_incl_tax_' . strtolower($countryCode);
            $this->assertDocumentContainsField($result, $priceWithTaxField, [$this->dummyPriceInclTax]);
        }
    }
}
