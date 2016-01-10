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

/**
 * @covers \LizardsAndPumpkins\Product\ProductSearch\ProductSearchDocumentBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 * @uses   \LizardsAndPumpkins\Product\ProductSearch\DefaultAttributeValueCollector
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 */
class ProductSearchDocumentBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeValueCollectorLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubValueCollectorLocator;

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
        return new ProductSearchDocumentBuilder($searchableAttributes, $this->stubValueCollectorLocator);
    }

    protected function setUp()
    {
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
}
