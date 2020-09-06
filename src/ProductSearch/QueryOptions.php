<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\ProductSearch\Exception\InvalidNumberOfProductsPerPageException;

class QueryOptions
{
    /**
     * @var array[]
     */
    private $filterSelection;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var FacetFiltersToIncludeInResult
     */
    private $facetFiltersToIncludeInResult;

    /**
     * @var int
     */
    private $rowsPerPage;

    /**
     * @var int
     */
    private $pageNumber;

    /**
     * @var SortBy
     */
    private $sortBy;

    /**
     * @param array[] $filterSelection
     * @param Context $context
     * @param FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @param SortBy $sortBy
     */
    private function __construct(
        array $filterSelection,
        Context $context,
        FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult,
        int $rowsPerPage,
        int $pageNumber,
        SortBy $sortBy
    ) {
        $this->filterSelection = $filterSelection;
        $this->context = $context;
        $this->facetFiltersToIncludeInResult = $facetFiltersToIncludeInResult;
        $this->rowsPerPage = $rowsPerPage;
        $this->pageNumber = $pageNumber;
        $this->sortBy = $sortBy;
    }

    /**
     * @param array[] $filterSelection
     * @param Context $context
     * @param FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @param SortBy $sortBy
     * @return QueryOptions
     */
    public static function create(
        array $filterSelection,
        Context $context,
        FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult,
        int $rowsPerPage,
        int $pageNumber,
        SortBy $sortBy
    ) {
        self::validateRowsPerPage($rowsPerPage);
        self::validatePageNumber($pageNumber);

        return new self(
            $filterSelection,
            $context,
            $facetFiltersToIncludeInResult,
            $rowsPerPage,
            $pageNumber,
            $sortBy
        );
    }

    /**
     * @return array[]
     */
    public function getFilterSelection(): array
    {
        return $this->filterSelection;
    }

    public function getContext() : Context
    {
        return $this->context;
    }

    public function getFacetFiltersToIncludeInResult() : FacetFiltersToIncludeInResult
    {
        return $this->facetFiltersToIncludeInResult;
    }

    public function getRowsPerPage() : int
    {
        return $this->rowsPerPage;
    }

    public function getPageNumber() : int
    {
        return $this->pageNumber;
    }

    public function getSortBy() : SortBy
    {
        return $this->sortBy;
    }

    private static function validateRowsPerPage(int $rowsPerPage): void
    {
        if ($rowsPerPage <= 0) {
            throw new InvalidNumberOfProductsPerPageException(
                sprintf('Number of rows per page must be positive, got "%s".', $rowsPerPage)
            );
        }
    }

    private static function validatePageNumber(int $pageNumber): void
    {
        if ($pageNumber < 0) {
            throw new InvalidNumberOfProductsPerPageException(
                sprintf('Current page number can not be negative, got "%s".', $pageNumber)
            );
        }
    }
}
