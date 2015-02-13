<?php

namespace Brera\Product;

use Brera\Environment\Environment;
use Brera\Environment\EnvironmentSource;
use Brera\KeyValue\SearchDocumentBuilder;
use Brera\ProjectionSourceData;
use Brera\SearchEngine\SearchEngine;

class ProductSearchDocumentBuilder implements SearchDocumentBuilder
{
    /**
     * @var SearchEngine
     */
    private $searchEngine;

    /**
     * @var string[]
     */
    private $attributesSchema;

    /**
     * @param SearchEngine $searchEngine
     * @param string[] $attributesSchema
     */
    public function __construct(SearchEngine $searchEngine, array $attributesSchema)
    {
        $this->searchEngine = $searchEngine;
        $this->attributesSchema = $attributesSchema;
    }

    /**
     * @param ProjectionSourceData $productSource
     * @param EnvironmentSource $environmentSource
     */
    public function aggregate(ProjectionSourceData $productSource, EnvironmentSource $environmentSource)
    {
        $productIndices = [];

        foreach ($environmentSource->extractEnvironments([]) as $environment) {
            $productIndices[] = $this->getProductIndexDataForEnvironment($productSource, $environment);
        }

        $this->searchEngine->addMultiToIndex($productIndices);
    }

    /**
     * @param ProductSource $productSource
     * @param Environment $environment
     * @return array
     */
    private function getProductIndexDataForEnvironment(ProductSource $productSource, Environment $environment)
    {
        $productInEnvironment = $productSource->getProductForEnvironment($environment);

        $productId = ['product_id' => $productInEnvironment->getId()->__toString()];
        $productAttributes = $this->getProductAttributes($productInEnvironment);
        $environmentPartsMap = $this->getEnvironmentPartsMap($environment);

        return array_merge($productId, $productAttributes, $environmentPartsMap);
    }

    /**
     * @param Product $productInEnvironment
     * @return string[]
     */
    private function getProductAttributes(Product $productInEnvironment)
    {
        $attributesMap = [];

        foreach ($this->attributesSchema as $attributeCode) {
            try {
                $attributesMap[$attributeCode] = $productInEnvironment->getAttributeValue($attributeCode);
            } catch (\Exception $e) {

            }
        }

        return $attributesMap;
    }

    /**
     * @param Environment $environment
     * @return string[]
     */
    private function getEnvironmentPartsMap(Environment $environment)
    {
        $environmentPartsMap = [];

        foreach ($environment->getSupportedCodes() as $environmentCode) {
            $environmentPartsMap[$environmentCode] = $environment->getValue($environmentCode);
        }

        return $environmentPartsMap;
    }
}
