<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;

class ProductSearchDocumentBuilder implements SearchDocumentBuilder
{
    /**
     * @var string[]
     */
    private $indexAttributeCodes;

    /**
     * @param string[] $indexAttributeCodes
     */
    public function __construct(array $indexAttributeCodes)
    {
        $this->indexAttributeCodes = $indexAttributeCodes;
    }

    /**
     * @param mixed $projectionSourceData
     * @return SearchDocumentCollection
     */
    public function aggregate($projectionSourceData)
    {
        if (!($projectionSourceData instanceof SimpleProduct)) {
            throw new InvalidProjectionSourceDataTypeException('First argument must be a Product instance.');
        }

        $searchDocument = $this->createSearchDocument($projectionSourceData);

        return new SearchDocumentCollection($searchDocument);
    }

    /**
     * @param SimpleProduct $product
     * @return SearchDocument[]
     */
    private function createSearchDocument(SimpleProduct $product)
    {
        $fieldsCollection = $this->createSearchDocumentFieldsCollection($product);

        return new SearchDocument($fieldsCollection, $product->getContext(), $product->getId());
    }

    /**
     * @param SimpleProduct $product
     * @return SearchDocumentFieldCollection
     */
    private function createSearchDocumentFieldsCollection(SimpleProduct $product)
    {
        $attributesMap = array_reduce($this->indexAttributeCodes, function ($carry, $attributeCode) use ($product) {
            $codeAndValues = [$attributeCode => $this->getAttributeValuesForSearchDocument($product, $attributeCode)];
            return array_merge($carry, $codeAndValues);
        }, []);

        return SearchDocumentFieldCollection::fromArray($attributesMap);
    }

    /**
     * @param SimpleProduct $product
     * @param string $attributeCode
     * @return array[]
     */
    private function getAttributeValuesForSearchDocument(SimpleProduct $product, $attributeCode)
    {
        return array_filter($product->getAllValuesOfAttribute($attributeCode), function ($value) {
            return is_scalar($value);
        });
    }
}
