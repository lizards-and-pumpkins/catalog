<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductRelations\ContentDelivery;

use LizardsAndPumpkins\ProductRelations\ProductRelations;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionNotEqual;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;

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

    public function __construct(DataPoolReader $dataPoolReader, SnippetKeyGenerator $productJsonSnippetKeyGenerator)
    {
        $this->dataPoolReader = $dataPoolReader;
        $this->productJsonSnippetKeyGenerator = $productJsonSnippetKeyGenerator;
    }

    /**
     * @param Context $context
     * @param ProductId $productId
     * @return ProductId[]
     */
    public function getById(Context $context, ProductId $productId) : array
    {
        $key = $this->productJsonSnippetKeyGenerator->getKeyForContext($context, [Product::ID => $productId]);
        $productData = json_decode($this->dataPoolReader->getSnippet($key), true);

        return $this->hasRequiredAttributes($productData) ?
            $this->getMatchingProductIds($context, $productData) :
            [];
    }

    /**
     * @param array[] $productData
     * @return bool
     */
    private function hasRequiredAttributes(array $productData) : bool
    {
        return
            isset($productData['attributes']['series']) &&
            isset($productData['attributes']['brand']) &&
            isset($productData['attributes']['gender']);
    }

    /**
     * @param Context $context
     * @param array[] $productData
     * @return ProductId[]
     */
    private function getMatchingProductIds(Context $context, array $productData) : array
    {
        $criteria = $this->createCriteria($productData);
        $sortBy = $this->createCreatedAtSortBy();
        $rowsPerPage = 20;
        $pageNumber = 0;

        return $this->dataPoolReader->getProductIdsMatchingCriteria(
            $criteria,
            $context,
            $sortBy,
            $rowsPerPage,
            $pageNumber
        );
    }

    /**
     * @param mixed[] $productData
     * @return CompositeSearchCriterion
     */
    private function createCriteria(array $productData) : CompositeSearchCriterion
    {
        return CompositeSearchCriterion::createAnd(
            $this->getBrandCriteria($productData),
            $this->getGenderCriteria($productData),
            $this->getSeriesCriteria($productData),
            new SearchCriterionNotEqual('product_id', $productData['product_id']),
            CompositeSearchCriterion::createOr(
                new SearchCriterionGreaterThan('stock_qty', 0),
                new SearchCriterionEqual('backorders', 'true')
            )
        );
    }

    /**
     * @param mixed[] $productData
     * @return SearchCriteria
     */
    private function getBrandCriteria(array $productData) : SearchCriteria
    {
        return $this->createSearchCriteriaMatching('brand', $productData['attributes']['brand']);
    }

    /**
     * @param mixed[] $productData
     * @return SearchCriteria
     */
    private function getGenderCriteria(array $productData) : SearchCriteria
    {
        return $this->createSearchCriteriaMatching('gender', $productData['attributes']['gender']);
    }

    /**
     * @param mixed[] $productData
     * @return SearchCriteria
     */
    private function getSeriesCriteria(array $productData) : SearchCriteria
    {
        return $this->createSearchCriteriaMatching('series', $productData['attributes']['series']);
    }

    /**
     * @param string $attributeCode
     * @param string|string[] $valueToMatch
     * @return SearchCriteria
     */
    private function createSearchCriteriaMatching(string $attributeCode, $valueToMatch) : SearchCriteria
    {
        if (is_array($valueToMatch)) {
            return CompositeSearchCriterion::createOr(...array_map(function ($value) use ($attributeCode) {
                return $this->createSearchCriteriaMatching($attributeCode, $value);
            }, $valueToMatch));
        }
        return new SearchCriterionEqual($attributeCode, $valueToMatch);
    }

    private function createCreatedAtSortBy() : SortBy
    {
        return new SortBy(AttributeCode::fromString('created_at'), SortDirection::create(SortDirection::DESC));
    }
}
