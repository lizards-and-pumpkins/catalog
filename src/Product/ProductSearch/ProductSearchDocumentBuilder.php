<?php

namespace LizardsAndPumpkins\Product\ProductSearch;

use LizardsAndPumpkins\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\Product;

class ProductSearchDocumentBuilder implements SearchDocumentBuilder
{
    /**
     * @var string[]
     */
    private $indexAttributeCodes;

    /**
     * @var AttributeValueCollectorLocator
     */
    private $valueCollectorLocator;

    /**
     * @param string[] $indexAttributeCodes
     * @param AttributeValueCollectorLocator $valueCollector
     */
    public function __construct(array $indexAttributeCodes, AttributeValueCollectorLocator $valueCollector)
    {
        $this->indexAttributeCodes = $indexAttributeCodes;
        $this->valueCollectorLocator = $valueCollector;
    }

    /**
     * @param Product $projectionSourceData
     * @return SearchDocument
     */
    public function aggregate($projectionSourceData)
    {
        if (!($projectionSourceData instanceof Product)) {
            throw new InvalidProjectionSourceDataTypeException('First argument must be a Product instance.');
        }

        return $this->createSearchDocument($projectionSourceData);
    }

    /**
     * @param Product $product
     * @return SearchDocument
     */
    private function createSearchDocument(Product $product)
    {
        $fieldsCollection = $this->createSearchDocumentFieldsCollection($product);

        return new SearchDocument($fieldsCollection, $product->getContext(), $product->getId());
    }

    /**
     * @param Product $product
     * @return SearchDocumentFieldCollection
     */
    private function createSearchDocumentFieldsCollection(Product $product)
    {
        $attributesMap = array_reduce($this->indexAttributeCodes, function ($carry, $attributeCode) use ($product) {
            $codeAndValues = [$attributeCode => $this->getAttributeValuesForSearchDocument($product, $attributeCode)];
            return array_merge($carry, $codeAndValues);
        }, []);

        return SearchDocumentFieldCollection::fromArray(
            array_merge($attributesMap, ['product_id' => (string) $product->getId()])
        );
    }

    /**
     * @param Product $product
     * @param string $attributeCode
     * @return array[]
     */
    private function getAttributeValuesForSearchDocument(Product $product, $attributeCode)
    {
        $collector = $this->valueCollectorLocator->forProduct($product);
        return $collector->getValues($product, AttributeCode::fromString($attributeCode));
    }
}
