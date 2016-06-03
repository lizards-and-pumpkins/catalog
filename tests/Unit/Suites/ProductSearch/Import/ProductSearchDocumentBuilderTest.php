<?php

namespace LizardsAndPumpkins\ProductSearch\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\Import\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Tax\TaxService;
use LizardsAndPumpkins\Import\Tax\TaxServiceLocator;
use LizardsAndPumpkins\Import\Tax\TaxableCountries;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\Import\ProductSearchDocumentBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\DefaultAttributeValueCollector
 * @uses   \LizardsAndPumpkins\Import\Price\Price
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 */
class ProductSearchDocumentBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $dummyTaxableCountries = ['DE', 'UK'];

    private $dummyPriceInclTax = '12199';

    /**
     * @var AttributeValueCollectorLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubValueCollectorLocator;

    /**
     * @var TaxableCountries|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubTaxableCountries;

    /**
     * @var TaxServiceLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubTaxServiceLocator;

    /**
     * @var TaxService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubTaxService;

    /**
     * @param array[] $attributesMap
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProduct(array $attributesMap)
    {
        $stubProductId = $this->createMock(ProductId::class);
        $stubProductId->method('__toString')->willReturn('test-id');
        $stubProduct = $this->createMock(Product::class);
        $stubProduct->method('getAllValuesOfAttribute')->willReturnMap($attributesMap);
        $stubProduct->method('getContext')->willReturn($this->createMock(Context::class));
        $stubProduct->method('getId')->willReturn($stubProductId);
        $stubProduct->method('hasAttribute')->willReturnCallback(function ($attributeCode) use ($attributesMap) {
            foreach ($attributesMap as $attributeMap) {
                if ($attributeMap[0] === $attributeCode) {
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
    private function assertDocumentContainsField(SearchDocument $document, $attributeCode, array $attributeValues)
    {
        $searchDocumentField = SearchDocumentField::fromKeyAndValues($attributeCode, $attributeValues);
        $this->assertContains($searchDocumentField, $document->getFieldsCollection()->getFields(), '', false, false);
    }

    /**
     * @param string[] $searchableAttributes
     * @return ProductSearchDocumentBuilder
     */
    private function createInstance(array $searchableAttributes)
    {
        return new ProductSearchDocumentBuilder(
            $searchableAttributes,
            $this->stubValueCollectorLocator,
            $this->stubTaxableCountries,
            $this->stubTaxServiceLocator
        );
    }

    protected function setUp()
    {
        $this->stubTaxableCountries = $this->createMock(TaxableCountries::class);
        $this->stubTaxableCountries->method('getCountries')->willReturn($this->dummyTaxableCountries);
        $this->stubTaxService = $this->createMock(TaxService::class);
        $this->stubTaxService->method('applyTo')->willReturn($this->dummyPriceInclTax);
        $this->stubTaxServiceLocator = $this->createMock(TaxServiceLocator::class);
        $this->stubTaxServiceLocator->method('get')->willReturn($this->stubTaxService);
        $this->stubValueCollectorLocator = $this->createMock(AttributeValueCollectorLocator::class);
        $this->stubValueCollectorLocator->method('forProduct')
            ->willReturn(new DefaultAttributeValueCollector());
    }

    public function testSearchDocumentBuilderInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SearchDocumentBuilder::class, $this->createInstance([]));
    }

    public function testExceptionIsThrownIfProjectionSourceDataIsNotProduct()
    {
        $this->expectException(InvalidProjectionSourceDataTypeException::class);
        $this->createInstance([])->aggregate('invalid-projection-source-data');
    }

    public function testSearchDocumentContainingIndexedAttributeIsReturned()
    {
        $searchableAttribute = 'foo';
        $attributeValues = ['bar'];

        $attributesMap = [[$searchableAttribute, $attributeValues]];
        $stubProduct = $this->createStubProduct($attributesMap);

        $searchDocumentBuilder = $this->createInstance([$searchableAttribute]);
        $result = $searchDocumentBuilder->aggregate($stubProduct);

        $this->assertInstanceOf(SearchDocument::class, $result);
        $this->assertDocumentContainsField($result, $searchableAttribute, $attributeValues);
    }

    public function testProductPriceIsIndexedIfProductHasNoSpecialPrice()
    {
        $priceAttributeCode = PriceSnippetRenderer::PRICE;
        $priceValues = ['1000'];

        $attributesMap = [[$priceAttributeCode, $priceValues]];
        $stubProduct = $this->createStubProduct($attributesMap);

        $searchDocumentBuilder = $this->createInstance([$priceAttributeCode]);
        $result = $searchDocumentBuilder->aggregate($stubProduct);

        $this->assertInstanceOf(SearchDocument::class, $result);
        $this->assertDocumentContainsField($result, $priceAttributeCode, $priceValues);
    }

    public function testProductSpecialPriceIsIndexedAsPriceIfProductHasSpecialPrice()
    {
        $priceAttributeCode = PriceSnippetRenderer::PRICE;
        $priceValues = ['1000'];

        $specialPriceAttributeCode = PriceSnippetRenderer::SPECIAL_PRICE;
        $specialPriceValues = ['900'];

        $attributesMap = [[$priceAttributeCode, $priceValues], [$specialPriceAttributeCode, $specialPriceValues]];
        $stubProduct = $this->createStubProduct($attributesMap);

        $searchDocumentBuilder = $this->createInstance([$priceAttributeCode]);
        $result = $searchDocumentBuilder->aggregate($stubProduct);

        $this->assertInstanceOf(SearchDocument::class, $result);
        $this->assertDocumentContainsField($result, $priceAttributeCode, $specialPriceValues);
    }

    public function testItIncludesTheProductIdInTheSearchDocumentFields()
    {
        $searchableAttribute = 'foo';
        $attributeValues = ['bar'];

        $attributesMap = [[$searchableAttribute, $attributeValues]];
        $stubProduct = $this->createStubProduct($attributesMap);

        $searchDocumentBuilder = $this->createInstance([$searchableAttribute]);
        $result = $searchDocumentBuilder->aggregate($stubProduct);

        $this->assertInstanceOf(SearchDocument::class, $result);
        $this->assertDocumentContainsField($result, 'product_id', [(string) $stubProduct->getId()]);
    }

    public function testItAddsThePriceIncludingTaxForEachTaxableCountry()
    {
        $priceField = 'price';
        $priceExcludingTax = ['100'];

        $attributesMap = [[$priceField, $priceExcludingTax]];
        $stubProduct = $this->createStubProduct($attributesMap);

        $searchDocumentBuilder = $this->createInstance([$priceField]);
        $result = $searchDocumentBuilder->aggregate($stubProduct);

        foreach ($this->dummyTaxableCountries as $countryCode) {
            $priceWithTaxField = 'price_incl_tax_' . strtolower($countryCode);
            $this->assertDocumentContainsField($result, $priceWithTaxField, [$this->dummyPriceInclTax]);
        }
    }
}
