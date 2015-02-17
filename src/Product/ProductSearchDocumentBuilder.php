<?php

namespace Brera\Product;

use Brera\Environment\Environment;
use Brera\Environment\EnvironmentSource;
use Brera\InvalidProjectionDataSourceType;
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
     * @param EnvironmentSource $environmentSource
     * @return SearchDocumentCollection
     * @throws InvalidProjectionDataSourceType
     */
    public function aggregate(ProjectionSourceData $productSource, EnvironmentSource $environmentSource)
    {
        if (!($productSource instanceof ProductSource)) {
            throw new InvalidProjectionDataSourceType('First argument must be instance of ProductSource.');
        }

        $collection = new SearchDocumentCollection();

        foreach ($environmentSource->extractEnvironments(['version', 'website', 'language']) as $environment) {
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

        foreach ($this->searchableAttributeCodes as $attributeCode) {
            $attributesMap[$attributeCode] = $product->getAttributeValue($attributeCode);
        }

        return SearchDocumentFieldCollection::fromArray($attributesMap);
    }
}
