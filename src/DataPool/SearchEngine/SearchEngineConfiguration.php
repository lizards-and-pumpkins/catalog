<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\Import\Product\AttributeCode;

class SearchEngineConfiguration
{
    /**
     * @var int
     */
    private $productsPerPage;

    /**
     * @var int
     */
    private $maxProductsPerPage;

    /**
     * @var SortBy
     */
    private $sortBy;

    /**
     * @var string[]
     */
    private $sortableAttributeCodes;

    public function __construct(
        int $productsPerPage,
        int $maxProductsPerPage,
        SortBy $sortBy,
        string ...$sortableAttributeCodes
    ) {
        $this->productsPerPage = $productsPerPage;
        $this->maxProductsPerPage = $maxProductsPerPage;
        $this->sortBy = $sortBy;
        $this->sortableAttributeCodes = $sortableAttributeCodes;
    }

    public function getProductsPerPage(): int
    {
        return $this->productsPerPage;
    }

    public function getMaxProductsPerPage(): int
    {
        return $this->maxProductsPerPage;
    }

    public function isExceedingMaxProductsPerPage(int $productsPerPage): bool
    {
        return $productsPerPage > $this->maxProductsPerPage;
    }

    public function getSortBy(): SortBy
    {
        return $this->sortBy;
    }

    public function isSortingByAttributeAllowed(AttributeCode $attributeCode): bool
    {
        return in_array((string) $attributeCode, $this->sortableAttributeCodes);
    }
}
