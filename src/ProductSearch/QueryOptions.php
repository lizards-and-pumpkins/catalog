<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
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
     * @var SortOrderConfig
     */
    private $sortOrderConfig;

    /**
     * @param array[] $filterSelection
     * @param Context $context
     * @param FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @param SortOrderConfig $sortOrderConfig
     */
    private function __construct(
        array $filterSelection,
        Context $context,
        FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult,
        int $rowsPerPage,
        int $pageNumber,
        SortOrderConfig $sortOrderConfig
    ) {
        $this->filterSelection = $filterSelection;
        $this->context = $context;
        $this->facetFiltersToIncludeInResult = $facetFiltersToIncludeInResult;
        $this->rowsPerPage = $rowsPerPage;
        $this->pageNumber = $pageNumber;
        $this->sortOrderConfig = $sortOrderConfig;
    }

    /**
     * @param array[] $filterSelection
     * @param Context $context
     * @param FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @param SortOrderConfig $sortOrderConfig
     * @return QueryOptions
     */
    public static function create(
        array $filterSelection,
        Context $context,
        FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult,
        int $rowsPerPage,
        int $pageNumber,
        SortOrderConfig $sortOrderConfig
    ) {
        self::validateRowsPerPage($rowsPerPage);
        self::validatePageNumber($pageNumber);

        return new self(
            $filterSelection,
            $context,
            $facetFiltersToIncludeInResult,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );
    }

    /**
     * @return array|\array[]
     */
    public function getFilterSelection()
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

    public function getSortOrderConfig() : SortOrderConfig
    {
        return $this->sortOrderConfig;
    }

    private static function validateRowsPerPage(int $rowsPerPage)
    {
        if ($rowsPerPage <= 0) {
            throw new InvalidNumberOfProductsPerPageException(
                sprintf('Number of rows per page must be positive, got "%s".', $rowsPerPage)
            );
        }
    }

    private static function validatePageNumber(int $pageNumber)
    {
        if ($pageNumber < 0) {
            throw new InvalidNumberOfProductsPerPageException(
                sprintf('Current page number can not be negative, got "%s".', $pageNumber)
            );
        }
    }
}
