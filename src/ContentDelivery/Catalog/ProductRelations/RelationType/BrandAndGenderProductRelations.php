<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\RelationType;

use LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations\ProductRelations;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionNotEqual;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\SimpleProduct;
use LizardsAndPumpkins\SnippetKeyGenerator;

class BrandAndGenderProductRelations implements ProductRelations
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
        $product = SimpleProduct::fromArray(json_decode($this->dataPoolReader->getSnippet($key), true));

        $criteria = $this->createCriteria($product);
        $sortBy = $this->createSortOrderConfig();
        $rowsPerPage = 5;
        $pageNumber = 1;
        
        return $this->dataPoolReader->getProductIdsMatchingCriteria(
            $criteria,
            $this->context,
            $sortBy,
            $rowsPerPage,
            $pageNumber
        );
    }

    /**
     * @param Product $product
     * @return CompositeSearchCriterion
     */
    private function createCriteria(Product $product)
    {
        return CompositeSearchCriterion::createAnd(
            SearchCriterionEqual::create('brand', $product->getFirstValueOfAttribute('brand')),
            SearchCriterionEqual::create('gender', $product->getFirstValueOfAttribute('gender')),
            SearchCriterionNotEqual::create('product_id', (string) $product->getId())
        );
    }

    /**
     * @return SortOrderConfig
     */
    private function createSortOrderConfig()
    {
        return SortOrderConfig::create(
            AttributeCode::fromString('created_at'),
            SortOrderDirection::create(SortOrderDirection::ASC)
        );
    }
}
