<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\RelationType;

use LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\ProductRelations;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionNotEqual;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\SnippetKeyGenerator;

class SameSeriesProductRelations implements ProductRelations
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var SnippetKeyGenerator
     */
    private $productJsonSnippetKeyGenerator;

    /**
     * @var Context
     */
    private $context;

    public function __construct(
        DataPoolReader $dataPoolReader,
        SnippetKeyGenerator $productJsonSnippetKeyGenerator,
        Context $context
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->productJsonSnippetKeyGenerator = $productJsonSnippetKeyGenerator;
        $this->context = $context;
    }

    /**
     * @param ProductId $productId
     * @return ProductId[]
     */
    public function getById(ProductId $productId)
    {
        $key = $this->productJsonSnippetKeyGenerator->getKeyForContext($this->context, [Product::ID => $productId]);
        $productData = json_decode($this->dataPoolReader->getSnippet($key), true);

        return $this->hasRequiredAttributes($productData) ?
            $this->getMatchingProductIds($productData) :
            [];
    }

    /**
     * @param array[] $productData
     * @return bool
     */
    private function hasRequiredAttributes(array $productData)
    {
        return
            isset($productData['attributes']['series']) &&
            isset($productData['attributes']['brand']) &&
            isset($productData['attributes']['gender']);
    }

    /**
     * @param array[] $productData
     * @return ProductId[]
     */
    private function getMatchingProductIds(array $productData)
    {
        $criteria = $this->createCriteria($productData);
        $sortBy = $this->createSortOrderConfig();
        $rowsPerPage = 20;
        $pageNumber = 0;

        return $this->dataPoolReader->getProductIdsMatchingCriteria(
            $criteria,
            $this->context,
            $sortBy,
            $rowsPerPage,
            $pageNumber
        );
    }

    /**
     * @param mixed[] $productData
     * @return CompositeSearchCriterion
     */
    private function createCriteria(array $productData)
    {
        return CompositeSearchCriterion::createAnd(
            $this->getBrandCriteria($productData),
            $this->getGenderCriteria($productData),
            $this->getSeriesCriteria($productData),
            SearchCriterionNotEqual::create('product_id', $productData['product_id'])
        );
    }

    /**
     * @param mixed[] $productData
     * @return SearchCriteria
     */
    private function getBrandCriteria(array $productData)
    {
        return $this->createSearchCriteriaMatching('brand', $productData['attributes']['brand']);
    }

    /**
     * @param mixed[] $productData
     * @return SearchCriteria
     */
    private function getGenderCriteria(array $productData)
    {
        return $this->createSearchCriteriaMatching('gender', $productData['attributes']['gender']);
    }

    /**
     * @param mixed[] $productData
     * @return SearchCriteria
     */
    private function getSeriesCriteria(array $productData)
    {
        return $this->createSearchCriteriaMatching('series', $productData['attributes']['series']);
    }

    /**
     * @param string $attributeCode
     * @param string|string[] $valueToMatch
     * @return SearchCriteria
     */
    private function createSearchCriteriaMatching($attributeCode, $valueToMatch)
    {
        if (is_array($valueToMatch)) {
            return CompositeSearchCriterion::createOr(...array_map(function ($value) use ($attributeCode) {
                return $this->createSearchCriteriaMatching($attributeCode, $value);
            }, $valueToMatch));
        }
        return SearchCriterionEqual::create($attributeCode, $valueToMatch);
    }

    /**
     * @return SortOrderConfig
     */
    private function createSortOrderConfig()
    {
        return SortOrderConfig::create(
            AttributeCode::fromString('created_at'),
            SortOrderDirection::create(SortOrderDirection::DESC)
        );
    }
}
