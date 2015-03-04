<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextSource;
use Brera\InvalidProjectionDataSourceTypeException;
use Brera\DataPool\SearchEngine\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocumentBuilder;
use Brera\DataPool\SearchEngine\SearchDocumentCollection;
use Brera\DataPool\SearchEngine\SearchDocumentFieldCollection;
use Brera\ProjectionSourceData;

class ProductSearchDocumentBuilder implements SearchDocumentBuilder
{
    /**
     * @var string[]
     */
    private $searchableAttributeCodes;

    /**
     * @param string[] $searchableAttributeCodes
     */
    public function __construct(array $searchableAttributeCodes)
    {
        $this->searchableAttributeCodes = $searchableAttributeCodes;
    }

    /**
     * @param ProjectionSourceData $productSource
     * @param ContextSource $contextSource
     * @return SearchDocumentCollection
     * @throws InvalidProjectionDataSourceTypeException
     */
    public function aggregate(ProjectionSourceData $productSource, ContextSource $contextSource)
    {
        if (!($productSource instanceof ProductSource)) {
            throw new InvalidProjectionDataSourceTypeException('First argument must be instance of ProductSource.');
        }

        $collection = new SearchDocumentCollection();

        foreach ($contextSource->getAllAvailableContexts() as $context) {
            $document = $this->createSearchDocument($productSource, $context);
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

        foreach ($this->searchableAttributeCodes as $attributeCode) {
            $attributesMap[$attributeCode] = $product->getAttributeValue($attributeCode);
        }

        return SearchDocumentFieldCollection::fromArray($attributesMap);
    }
}
