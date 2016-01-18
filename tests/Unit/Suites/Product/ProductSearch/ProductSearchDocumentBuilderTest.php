<?php

namespace LizardsAndPumpkins\Product\ProductSearch;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Product\PriceSnippetRenderer;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\Tax\TaxService;
use LizardsAndPumpkins\Product\Tax\TaxServiceLocator;
use LizardsAndPumpkins\TaxableCountries;

/**
 * @covers \LizardsAndPumpkins\Product\ProductSearch\ProductSearchDocumentBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 * @uses   \LizardsAndPumpkins\Product\ProductSearch\DefaultAttributeValueCollector
 * @uses   \LizardsAndPumpkins\Product\Price
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
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
        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $stubProductId->method('__toString')->willReturn('test-id');
        $stubProduct = $this->getMock(Product::class);
        $stubProduct->method('getAllValuesOfAttribute')->willReturnMap($attributesMap);
        $stubProduct->method('getContext')->willReturn($this->getMock(Context::class));
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
        $this->stubTaxableCountries = $this->getMock(TaxableCountries::class);
        $this->stubTaxableCountries->method('getCountries')->willReturn($this->dummyTaxableCountries);
        $this->stubTaxService = $this->getMock(TaxService::class);
        $this->stubTaxService->method('applyTo')->willReturn($this->dummyPriceInclTax);
        $this->stubTaxServiceLocator = $this->getMock(TaxServiceLocator::class);
        $this->stubTaxServiceLocator->method('get')->willReturn($this->stubTaxService);
        $this->stubValueCollectorLocator = $this->getMock(AttributeValueCollectorLocator::class, [], [], '', false);
        $this->stubValueCollectorLocator->method('forProduct')
            ->willReturn(new DefaultAttributeValueCollector());
    }

    public function testSearchDocumentBuilderInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SearchDocumentBuilder::class, $this->createInstance([]));
    }

    public function testExceptionIsThrownIfProjectionSourceDataIsNotProduct()
    {
        $this->setExpectedException(InvalidProjectionSourceDataTypeException::class);
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
