<?php

namespace Brera\Product;

use Brera\Environment\Environment;
use Brera\Environment\EnvironmentSource;
use Brera\InvalidProjectionDataSourceType;
use Brera\KeyValue\SearchDocument;
use Brera\KeyValue\SearchDocumentBuilder;
use Brera\KeyValue\SearchDocumentCollection;
use Brera\KeyValue\SearchDocumentFieldCollection;
use Brera\ProjectionSourceData;

class ProductSearchDocumentBuilder implements SearchDocumentBuilder
{
    /**
     * @var string[]
     */
    private $attributesSchema;

    /**
     * @param string[] $attributesSchema
     */
    public function __construct(array $attributesSchema)
    {
        $this->attributesSchema = $attributesSchema;
    }

    /**
     * @param ProjectionSourceData $productSource
     * @param EnvironmentSource $environmentSource
     * @return SearchDocumentCollection
     * @throws InvalidProjectionDataSourceType
     */
    public function aggregate(ProjectionSourceData $productSource, EnvironmentSource $environmentSource)
    {
        if (!($productSource instanceof ProductSource)) {
            throw new InvalidProjectionDataSourceType('First argument must be instance of Product.');
        }

        $collection = new SearchDocumentCollection();

        foreach ($environmentSource->extractEnvironments([]) as $environment) {
            $document = $this->createSearchDocument($productSource, $environment);
            $collection->add($document);
        }

        return $collection;
    }

    /**
     * @param ProductSource $productSource
     * @param Environment $environment
     * @return SearchDocument
     */
    private function createSearchDocument(ProductSource $productSource, Environment $environment)
    {
        $product = $productSource->getProductForEnvironment($environment);
        $fieldsCollection = $this->createSearchDocumentFieldsCollection($product);

        return new SearchDocument($fieldsCollection, $environment, $product->getId());
    }

    /**
     * @param Product $product
     * @return SearchDocumentFieldCollection
     */
    private function createSearchDocumentFieldsCollection(Product $product)
    {
        $attributesMap = [];

        foreach ($this->attributesSchema as $attributeCode) {
            $attributesMap[$attributeCode] = $product->getAttributeValue($attributeCode);
        }

        return SearchDocumentFieldCollection::fromArray($attributesMap);
    }
}
