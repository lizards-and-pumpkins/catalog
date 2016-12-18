<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\SearchFieldToRequestParamMap;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Import\Product\AttributeCode;

class ProductListingPageRequest
{
    const PRODUCTS_PER_PAGE_COOKIE_NAME = 'products_per_page';
    const PRODUCTS_PER_PAGE_COOKIE_TTL = 3600 * 24 * 30;
    const PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME = 'limit';
    const SORT_ORDER_COOKIE_NAME = 'sort_order';
    const SORT_DIRECTION_COOKIE_NAME = 'sort_direction';
    const SORT_ORDER_COOKIE_TTL = 3600 * 24 * 30;
    const SORT_DIRECTION_COOKIE_TTL = 3600 * 24 * 30;
    const SORT_ORDER_QUERY_PARAMETER_NAME = 'order';
    const SORT_DIRECTION_QUERY_PARAMETER_NAME = 'dir';
    const PAGINATION_QUERY_PARAMETER_NAME = 'p';

    /**
     * @var SearchFieldToRequestParamMap
     */
    private $searchFieldToRequestParamMap;

    /**
     * @var ProductsPerPage
     */
    private $productsPerPage;

    public function __construct(
        ProductsPerPage $productsPerPage,
        SearchFieldToRequestParamMap $searchFieldToRequestParamMap
    ) {
        $this->productsPerPage = $productsPerPage;
        $this->searchFieldToRequestParamMap = $searchFieldToRequestParamMap;
    }

    public function getCurrentPageNumber(HttpRequest $request) : int
    {
        if (! $request->hasQueryParameter(self::PAGINATION_QUERY_PARAMETER_NAME)) {
            return 0;
        }

        return max(0, $request->getQueryParameter(self::PAGINATION_QUERY_PARAMETER_NAME) - 1);
    }

    /**
     * @param HttpRequest $request
     * @param FacetFiltersToIncludeInResult $facetFilterRequest
     * @return array[]
     */
    public function getSelectedFilterValues(
        HttpRequest $request,
        FacetFiltersToIncludeInResult $facetFilterRequest
    ) : array {
        $facetFilterAttributeCodeStrings = $facetFilterRequest->getAttributeCodeStrings();
        return array_reduce($facetFilterAttributeCodeStrings, function (array $carry, $filterName) use ($request) {
            $queryParameterName = $this->searchFieldToRequestParamMap->getQueryParameterName($filterName);
            $carry[$filterName] = $this->getFilterValuesFromRequest($request, $queryParameterName);
            return $carry;
        }, []);
    }

    /**
     * @param HttpRequest $request
     * @param string $queryParameterName
     * @return string[]
     */
    private function getFilterValuesFromRequest(HttpRequest $request, string $queryParameterName) : array
    {
        if (! $request->hasQueryParameter($queryParameterName)) {
            return [];
        }

        return array_filter(explode(',', (string) $request->getQueryParameter($queryParameterName)));
    }

    public function getProductsPerPage(HttpRequest $request) : ProductsPerPage
    {
        if ($request->hasQueryParameter(self::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME)) {
            $availableNumbersOfProductsPerPage = $this->productsPerPage->getNumbersOfProductsPerPage();
            $numberOfProductsPerPage = $request->getQueryParameter(self::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME);
            return ProductsPerPage::create($availableNumbersOfProductsPerPage, (int) $numberOfProductsPerPage);
        }

        if ($request->hasCookie(self::PRODUCTS_PER_PAGE_COOKIE_NAME)) {
            $availableNumbersOfProductsPerPage = $this->productsPerPage->getNumbersOfProductsPerPage();
            $selected = (int) $request->getCookieValue(self::PRODUCTS_PER_PAGE_COOKIE_NAME);
            return ProductsPerPage::create($availableNumbersOfProductsPerPage, $selected);
        }

        return $this->productsPerPage;
    }

    public function getSelectedSortBy(HttpRequest $request, SortBy $defaultSortBy, SortBy ...$availableSortBy) : SortBy
    {
        if ($this->sortingIsPresentInQuery($request)) {
            $queryStringSortOrder = $request->getQueryParameter(self::SORT_ORDER_QUERY_PARAMETER_NAME);
            $queryStringSortDirection = $request->getQueryParameter(self::SORT_DIRECTION_QUERY_PARAMETER_NAME);

            if ($this->isValidSortOrder($queryStringSortOrder, $queryStringSortDirection, ...$availableSortBy)) {
                return $this->createSortBy($queryStringSortOrder, $queryStringSortDirection);
            }
        }

        if ($request->hasCookie(self::SORT_ORDER_COOKIE_NAME) &&
            $request->hasCookie(self::SORT_DIRECTION_COOKIE_NAME)
        ) {
            $cookieSortOrder = $request->getCookieValue(self::SORT_ORDER_COOKIE_NAME);
            $cookieDirection = $request->getCookieValue(self::SORT_DIRECTION_COOKIE_NAME);

            if ($this->isValidSortOrder($cookieSortOrder, $cookieDirection, ...$availableSortBy)) {
                return $this->createSortBy($cookieSortOrder, $cookieDirection);
            }
        }

        return $defaultSortBy;
    }

    public function processCookies(HttpRequest $request, SortBy ...$availableSortBy)
    {
        if ($request->hasQueryParameter(self::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME)) {
            setcookie(
                self::PRODUCTS_PER_PAGE_COOKIE_NAME,
                $request->getQueryParameter(self::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME),
                time() + self::PRODUCTS_PER_PAGE_COOKIE_TTL
            );
        }

        if ($this->sortingIsPresentInQuery($request)) {

            $sortOrder = $request->getQueryParameter(self::SORT_ORDER_QUERY_PARAMETER_NAME);
            $sortDirection = $request->getQueryParameter(self::SORT_DIRECTION_QUERY_PARAMETER_NAME);

            if ($this->isValidSortOrder($sortOrder, $sortDirection, ...$availableSortBy)) {
                setcookie(self::SORT_ORDER_COOKIE_NAME, $sortOrder, time() + self::SORT_ORDER_COOKIE_TTL);
                setcookie(self::SORT_DIRECTION_COOKIE_NAME, $sortDirection, time() + self::SORT_DIRECTION_COOKIE_TTL);
            }
        }
    }

    public function createSortByForRequest(SortBy $sortBy) : SortBy
    {
        $attributeCodeString = (string) $sortBy->getAttributeCode();
        $mappedAttributeCodeString = $this->searchFieldToRequestParamMap->getSearchFieldName($attributeCodeString);
        $attributeCode = AttributeCode::fromString($mappedAttributeCodeString);

        return new SortBy($attributeCode, $sortBy->getSelectedDirection());
    }

    private function createSortBy(string $attributeCode, string $direction) : SortBy
    {
        return new SortBy(AttributeCode::fromString($attributeCode), SortDirection::create($direction));
    }

    private function sortingIsPresentInQuery(HttpRequest $request) : bool
    {
        return $request->hasQueryParameter(self::SORT_ORDER_QUERY_PARAMETER_NAME) &&
               $request->hasQueryParameter(self::SORT_DIRECTION_QUERY_PARAMETER_NAME);
    }

    private function isValidSortOrder(string $sortOrder, string $direction, SortBy ...$availableSortBy) : bool
    {
        foreach ($availableSortBy as $config) {
            if ($config->getAttributeCode()->isEqualTo($sortOrder) && SortDirection::isValid($direction)) {
                return true;
            }
        }

        return false;
    }
}
