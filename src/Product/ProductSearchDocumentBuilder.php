<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
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
     * @param ContextSource $contextSource
     * @return SearchDocumentCollection
     */
    public function aggregate($projectionSourceData, ContextSource $contextSource)
    {
        if (!($projectionSourceData instanceof ProductSource)) {
            throw new InvalidProjectionSourceDataTypeException('First argument must be instance of ProductSource.');
        }

        $searchDocuments = $this->createSearchDocuments($projectionSourceData, $contextSource);

        return new SearchDocumentCollection(...$searchDocuments);
    }

    /**
     * @param ProductSource $productSource
     * @param ContextSource $contextSource
     * @return SearchDocument[]
     */
    private function createSearchDocuments(ProductSource $productSource, ContextSource $contextSource)
    {
        return array_map(function (Context $context) use ($productSource) {
            return $this->createSearchDocument($productSource, $context);
        }, $contextSource->getAllAvailableContexts());
    }

    /**
     * @param ProductSource $productSource
     * @param Context $context
     * @return SearchDocument
     */
    private function createSearchDocument(ProductSource $productSource, Context $context)
    {
        $product = $productSource->getProductForContext($context);
        $fieldsCollection = $this->createSearchDocumentFieldsCollection($product);

        return new SearchDocument($fieldsCollection, $context, $product->getId());
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

        return SearchDocumentFieldCollection::fromArray($attributesMap);
    }

    /**
     * @param Product $product
     * @param string $attributeCode
     * @return array[]
     */
    private function getAttributeValuesForSearchDocument(Product $product, $attributeCode)
    {
        return array_filter($product->getAllValuesOfAttribute($attributeCode), function ($value) {
            return is_scalar($value);
        });
    }
}
