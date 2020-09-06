<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\Core\Factory\Factory;
use LizardsAndPumpkins\Core\Factory\FactoryTrait;

class ProductSearchSharedFactory implements Factory
{
    use FactoryTrait;

    public function createProductSearchService(): ProductSearchService
    {
        return new ProductSearchService(
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()->createGlobalProductListingCriteria(),
            $this->getMasterFactory()->createProductJsonService()
        );
    }

    public function createFullTextCriteriaBuilder(): FullTextCriteriaBuilder
    {
        return new DefaultFullTextCriteriaBuilder(
            $this->getMasterFactory()->getFullTextSearchWordCombinationOperator()
        );
    }

    public function getFullTextSearchWordCombinationOperator(): string
    {
        return CompositeSearchCriterion::OR_CONDITION;
    }
}
