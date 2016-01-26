<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\Context\Context;

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
     * QueryOptions constructor.
     *
     * @param array[] $filterSelection
     * @param Context $context
     * @param FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult
     * @param int $rowsPerPage
     * @param int $pageNumber
     * @param SortOrderConfig $sortOrderConfig
     */
    public function __construct(
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
}
