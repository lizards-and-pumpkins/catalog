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
            $carry[$filterName] = array_filter(explode(',', (string) $request->getQueryParameter($queryParameterName)));
            return $carry;
        }, []);
    }

    public function getProductsPerPage(HttpRequest $request) : ProductsPerPage
    {
        $productsPerPageQueryStringValue = $this->getProductsPerPageQueryStringValue($request);
        if (null !== $productsPerPageQueryStringValue) {
            $numbersOfProductsPerPage = $this->productsPerPage->getNumbersOfProductsPerPage();
            return ProductsPerPage::create($numbersOfProductsPerPage, (int) $productsPerPageQueryStringValue);
        }

        if ($request->hasCookie(self::PRODUCTS_PER_PAGE_COOKIE_NAME)) {
            $numbersOfProductsPerPage = $this->productsPerPage->getNumbersOfProductsPerPage();
            $selected = (int) $request->getCookieValue(self::PRODUCTS_PER_PAGE_COOKIE_NAME);
            return ProductsPerPage::create($numbersOfProductsPerPage, $selected);
        }

        return $this->productsPerPage;
    }

    public function getSelectedSortBy(HttpRequest $request, SortBy $defaultSortBy, SortBy ...$availableSortBy) : SortBy
    {
        $sortOrderQueryStringValue = $this->getSortOrderQueryStringValue($request);
        $sortDirectionQueryStringValue = $this->getSortDirectionQueryStringValue($request);

        if (null !== $sortOrderQueryStringValue &&
            null !== $sortDirectionQueryStringValue &&
            $this->isValidSortOrder($sortOrderQueryStringValue, $sortDirectionQueryStringValue, ...$availableSortBy)
        ) {
            return $this->createSortBy($sortOrderQueryStringValue, $sortDirectionQueryStringValue);
        }

        if ($request->hasCookie(self::SORT_ORDER_COOKIE_NAME) &&
            $request->hasCookie(self::SORT_DIRECTION_COOKIE_NAME)
        ) {
            $sortOrder = $request->getCookieValue(self::SORT_ORDER_COOKIE_NAME);
            $direction = $request->getCookieValue(self::SORT_DIRECTION_COOKIE_NAME);

            if ($this->isValidSortOrder($sortOrder, $direction, ...$availableSortBy)) {
                return $this->createSortBy($sortOrder, $direction);
            }
        }

        return $defaultSortBy;
    }

    public function processCookies(HttpRequest $request, SortBy ...$availableSortBy)
    {
        $productsPerPage = $this->getProductsPerPageQueryStringValue($request);

        if ($productsPerPage !== null) {
            setcookie(
                self::PRODUCTS_PER_PAGE_COOKIE_NAME,
                $productsPerPage,
                time() + self::PRODUCTS_PER_PAGE_COOKIE_TTL
            );
        }

        $sortOrder = $this->getSortOrderQueryStringValue($request);
        $sortDirection = $this->getSortDirectionQueryStringValue($request);

        if (null !== $sortOrder &&
            null !== $sortDirection &&
            $this->isValidSortOrder($sortOrder, $sortDirection, ...$availableSortBy)
        ) {
            setcookie(self::SORT_ORDER_COOKIE_NAME, $sortOrder, time() + self::SORT_ORDER_COOKIE_TTL);
            setcookie(self::SORT_DIRECTION_COOKIE_NAME, $sortDirection, time() + self::SORT_DIRECTION_COOKIE_TTL);
        }
    }

    public function createSortByForRequest(SortBy $sortBy) : SortBy
    {
        $attributeCodeString = (string) $sortBy->getAttributeCode();
        $mappedAttributeCodeString = $this->searchFieldToRequestParamMap->getSearchFieldName($attributeCodeString);
        $attributeCode = AttributeCode::fromString($mappedAttributeCodeString);

        return new SortBy($attributeCode, $sortBy->getSelectedDirection());
    }

    /**
     * @param HttpRequest $request
     * @return null|string
     */
    private function getProductsPerPageQueryStringValue(HttpRequest $request)
    {
        return $request->getQueryParameter(self::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME);
    }

    private function createSortBy(string $attributeCode, string $direction) : SortBy
    {
        return new SortBy(AttributeCode::fromString($attributeCode), SortDirection::create($direction));
    }

    /**
     * @param HttpRequest $request
     * @return null|string
     */
    private function getSortOrderQueryStringValue(HttpRequest $request)
    {
        return $request->getQueryParameter(self::SORT_ORDER_QUERY_PARAMETER_NAME);
    }

    /**
     * @param HttpRequest $request
     * @return null|string
     */
    private function getSortDirectionQueryStringValue(HttpRequest $request)
    {
        return $request->getQueryParameter(self::SORT_DIRECTION_QUERY_PARAMETER_NAME);
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
