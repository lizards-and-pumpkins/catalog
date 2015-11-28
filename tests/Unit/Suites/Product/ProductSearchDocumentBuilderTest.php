<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\Exception\InvalidProjectionSourceDataTypeException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductSearchDocumentBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 */
class ProductSearchDocumentBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array[] $attributesMap
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProduct(array $attributesMap)
    {
        $stubProduct = $this->getMock(Product::class);
        $stubProduct->method('getAllValuesOfAttribute')->willReturnMap($attributesMap);
        $stubProduct->method('getContext')->willReturn($this->getMock(Context::class));
        $stubProduct->method('getId')->willReturn($this->getMock(ProductId::class, [], [], '', false));
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

    public function testSearchDocumentBuilderInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SearchDocumentBuilder::class, new ProductSearchDocumentBuilder([]));
    }

    public function testExceptionIsThrownIfProjectionSourceDataIsNotProduct()
    {
        $this->setExpectedException(InvalidProjectionSourceDataTypeException::class);
        (new ProductSearchDocumentBuilder([]))->aggregate('invalid-projection-source-data');
    }

    public function testSearchDocumentCollectionWithDocumentContainingIndexedAttributeIsReturned()
    {
        $searchableAttributeCode = 'foo';
        $attributeValues = ['bar'];

        $attributesMap = [[$searchableAttributeCode, $attributeValues]];
        $stubProduct = $this->createStubProduct($attributesMap);

        $searchDocumentBuilder = new ProductSearchDocumentBuilder([$searchableAttributeCode]);
        $result = $searchDocumentBuilder->aggregate($stubProduct);

        $this->assertInstanceOf(SearchDocumentCollection::class, $result);
        $this->assertCount(1, $result);
        $this->assertDocumentContainsField($result->getDocuments()[0], $searchableAttributeCode, $attributeValues);
    }

    public function testProductPriceIsIndexedIfProductHasNoSpecialPrice()
    {
        $priceAttributeCode = PriceSnippetRenderer::PRICE;
        $priceValues = ['1000'];

        $attributesMap = [[$priceAttributeCode, $priceValues]];
        $stubProduct = $this->createStubProduct($attributesMap);

        $searchDocumentBuilder = new ProductSearchDocumentBuilder([$priceAttributeCode]);
        $result = $searchDocumentBuilder->aggregate($stubProduct);

        $this->assertInstanceOf(SearchDocumentCollection::class, $result);
        $this->assertCount(1, $result);
        $this->assertDocumentContainsField($result->getDocuments()[0], $priceAttributeCode, $priceValues);
    }

    public function testProductSpecialPriceIsIndexedAsPriceIfProductHasSpecialPrice()
    {
        $priceAttributeCode = PriceSnippetRenderer::PRICE;
        $priceValues = ['1000'];

        $specialPriceAttributeCode = PriceSnippetRenderer::SPECIAL_PRICE;
        $specialPriceValues = ['900'];

        $attributesMap = [[$priceAttributeCode, $priceValues], [$specialPriceAttributeCode, $specialPriceValues]];
        $stubProduct = $this->createStubProduct($attributesMap);

        $searchDocumentBuilder = new ProductSearchDocumentBuilder([$priceAttributeCode]);
        $result = $searchDocumentBuilder->aggregate($stubProduct);

        $this->assertInstanceOf(SearchDocumentCollection::class, $result);
        $this->assertCount(1, $result);
        $this->assertDocumentContainsField($result->getDocuments()[0], $priceAttributeCode, $specialPriceValues);
    }
}
