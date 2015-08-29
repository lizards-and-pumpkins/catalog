<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\InvalidProjectionSourceDataTypeException;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;

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

        $collection = new SearchDocumentCollection();

        foreach ($contextSource->getAllAvailableContexts() as $context) {
            $document = $this->createSearchDocument($projectionSourceData, $context);
            $collection->add($document);
        }

        return $collection;
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
        $attributesMap = [];

        foreach ($this->indexAttributeCodes as $attributeCode) {
            /* TODO: handle case when attribute has more then one value for attribute (e.g. gender, category) */
            $attributesMap[$attributeCode] = $product->getFirstValueOfAttribute($attributeCode);
        }

        return SearchDocumentFieldCollection::fromArray($attributesMap);
    }
}
