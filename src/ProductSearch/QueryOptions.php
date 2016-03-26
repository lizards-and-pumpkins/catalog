<?php

namespace LizardsAndPumpkins\ProductSearch;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\Exception\InvalidRowsPerPageException;

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
        $rowsPerPage,
        $pageNumber,
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
        $rowsPerPage,
        $pageNumber,
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

    public function getFilterSelection()
    {
        return $this->filterSelection;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getFacetFiltersToIncludeInResult()
    {
        return $this->facetFiltersToIncludeInResult;
    }

    public function getRowsPerPage()
    {
        return $this->rowsPerPage;
    }

    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    public function getSortOrderConfig()
    {
        return $this->sortOrderConfig;
    }

    private static function validateRowsPerPage($rowsPerPage)
    {
        if (!is_int($rowsPerPage)) {
            throw new InvalidRowsPerPageException(
                sprintf('Number of rows per page must be an integer, got "%s".', gettype($rowsPerPage))
            );
        }

        if ($rowsPerPage <= 0) {
            throw new InvalidRowsPerPageException(
                sprintf('Number of rows per page must be positive, got "%s".', $rowsPerPage)
            );
        }
    }

    private static function validatePageNumber($pageNumber)
    {
        if (!is_int($pageNumber)) {
            throw new InvalidRowsPerPageException(
                sprintf('Current page number must be an integer, got "%s".', gettype($pageNumber))
            );
        }

        if ($pageNumber < 0) {
            throw new InvalidRowsPerPageException(
                sprintf('Current page number can not be negative, got "%s".', $pageNumber)
            );
        }
    }
}
