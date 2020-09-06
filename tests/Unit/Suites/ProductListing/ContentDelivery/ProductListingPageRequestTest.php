<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\SearchFieldToRequestParamMap;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageRequest
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductsPerPage
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 */
class ProductListingPageRequestTest extends TestCase
{
    /**
     * @var ProductsPerPage|MockObject
     */
    private $stubProductsPerPage;

    /**
     * @var ProductListingPageRequest
     */
    private $pageRequest;

    /**
     * @var array[]
     */
    private static $setCookieValues = [];

    /**
     * @var HttpRequest|MockObject
     */
    private $stubRequest;

    /**
     * @var SearchFieldToRequestParamMap|MockObject
     */
    private $stubSearchFieldToRequestParamMap;

    /**
     * @param string $name
     * @param mixed $value
     * @param int $ttl
     */
    private function assertCookieHasBeenSet(string $name, $value, int $ttl): void
    {
        $this->assertTrue(in_array([$name, $value, time() + $ttl], self::$setCookieValues));
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param int $ttl
     */
    private function assertCookieHasNotBeenSet(string $name, $value, int $ttl): void
    {
        $this->assertNotContains([$name, $value, time() + $ttl], self::$setCookieValues);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param int $expire
     */
    public static function trackSetCookieCalls(string $name, $value, int $expire): void
    {
        self::$setCookieValues[] = [$name, $value, $expire];
    }

    final protected function setUp(): void
    {
        $this->stubProductsPerPage = $this->createMock(ProductsPerPage::class);
        $class = SearchFieldToRequestParamMap::class;
        $this->stubSearchFieldToRequestParamMap = $this->createMock($class);
        $this->pageRequest = new ProductListingPageRequest(
            $this->stubProductsPerPage,
            $this->stubSearchFieldToRequestParamMap
        );
        $this->stubRequest = $this->createMock(HttpRequest::class);
    }

    final protected function tearDown(): void
    {
        self::$setCookieValues = [];
    }

    public function testCurrentPageIsZeroByDefault(): void
    {
        $this->stubRequest->method('hasQueryParameter')
            ->with(ProductListingPageRequest::PAGINATION_QUERY_PARAMETER_NAME)->willReturn(false);
        $this->assertSame(0, $this->pageRequest->getCurrentPageNumber($this->stubRequest));
    }

    public function testCurrentPageIsZeroIfNegativePageNumberIsRequested(): void
    {
        $pageNumber = -2;

        $this->stubRequest->method('hasQueryParameter')
            ->with(ProductListingPageRequest::PAGINATION_QUERY_PARAMETER_NAME)->willReturn(true);
        $this->stubRequest->method('getQueryParameter')
            ->with(ProductListingPageRequest::PAGINATION_QUERY_PARAMETER_NAME)->willReturn((string) ($pageNumber + 1));

        $this->assertSame(0, $this->pageRequest->getCurrentPageNumber($this->stubRequest));

    }

    public function testReturnsCurrentPageNumber(): void
    {
        $pageNumber = 2;

        $this->stubRequest->method('hasQueryParameter')
            ->with(ProductListingPageRequest::PAGINATION_QUERY_PARAMETER_NAME)->willReturn(true);
        $this->stubRequest->method('getQueryParameter')
            ->with(ProductListingPageRequest::PAGINATION_QUERY_PARAMETER_NAME)->willReturn((string) ($pageNumber + 1));

        $this->assertSame($pageNumber, $this->pageRequest->getCurrentPageNumber($this->stubRequest));
    }

    public function testSelectedFiltersArrayIsReturned(): void
    {
        $this->stubSearchFieldToRequestParamMap->method('getQueryParameterName')->willReturnArgument(0);
        
        $filterAName = 'foo';
        $filterBName = 'bar';

        /** @var FacetFiltersToIncludeInResult|MockObject $stubFacetFilterRequest */
        $stubFacetFilterRequest = $this->createMock(FacetFiltersToIncludeInResult::class);
        $stubFacetFilterRequest->method('getAttributeCodeStrings')->willReturn([$filterAName, $filterBName]);

        $this->stubRequest->method('hasQueryParameter')->willReturnMap([[$filterAName, true], [$filterBName, false]]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([[$filterAName, 'baz,qux']]);

        $result = $this->pageRequest->getSelectedFilterValues($this->stubRequest, $stubFacetFilterRequest);
        $expectedFilterValues = ['foo' => ['baz', 'qux'], 'bar' => []];

        $this->assertSame($expectedFilterValues, $result);
    }

    public function testInitialProductsPerPageConfigurationIsReturned(): void
    {
        $this->stubRequest->method('hasQueryParameter')
            ->with(ProductListingPageRequest::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME)->willReturn(false);

        $this->assertSame($this->stubProductsPerPage, $this->pageRequest->getProductsPerPage($this->stubRequest));
    }

    public function testProductsPerPageSpecifiedInQueryStringIsReturned(): void
    {
        $selectedNumberOfProductsPerPage = 2;
        $availableNumbersOfProductsPerPage = [1, 2];

        $this->stubRequest->method('hasQueryParameter')
            ->with(ProductListingPageRequest::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME)->willReturn(true);
        $this->stubRequest->method('getQueryParameter')
            ->with(ProductListingPageRequest::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME)
            ->willReturn((string) $selectedNumberOfProductsPerPage);

        $this->stubProductsPerPage->method('getNumbersOfProductsPerPage')
            ->willReturn($availableNumbersOfProductsPerPage);

        $result = $this->pageRequest->getProductsPerPage($this->stubRequest);

        $this->assertEquals($availableNumbersOfProductsPerPage, $result->getNumbersOfProductsPerPage());
        $this->assertEquals($selectedNumberOfProductsPerPage, $result->getSelectedNumberOfProductsPerPage());
    }

    public function testProductsPerPageSpecifiedInCookieIsReturned(): void
    {
        $selectedNumberOfProductsPerPage = 2;
        $availableNumbersOfProductsPerPage = [1, 2];

        $this->stubRequest->method('hasCookie')->with(ProductListingPageRequest::PRODUCTS_PER_PAGE_COOKIE_NAME)
            ->willReturn(true);

        $this->stubRequest->method('getCookieValue')->with(ProductListingPageRequest::PRODUCTS_PER_PAGE_COOKIE_NAME)
            ->willReturn((string) $selectedNumberOfProductsPerPage);

        $this->stubProductsPerPage->method('getNumbersOfProductsPerPage')
            ->willReturn($availableNumbersOfProductsPerPage);

        $result = $this->pageRequest->getProductsPerPage($this->stubRequest);

        $this->assertEquals($availableNumbersOfProductsPerPage, $result->getNumbersOfProductsPerPage());
        $this->assertEquals($selectedNumberOfProductsPerPage, $result->getSelectedNumberOfProductsPerPage());
    }

    public function testReturnsDefaultSortBy(): void
    {
        $stubDefaultSortBy = $this->createMock(SortBy::class);
        $stubAvailableSortBy = [$stubDefaultSortBy, $this->createMock(SortBy::class)];

        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_QUERY_PARAMETER_NAME, false],
            [ProductListingPageRequest::SORT_DIRECTION_QUERY_PARAMETER_NAME, false],
        ]);

        $result = $this->pageRequest->getSelectedSortBy(
            $this->stubRequest,
            $stubDefaultSortBy,
            ...$stubAvailableSortBy
        );

        $this->assertSame($stubDefaultSortBy, $result);
    }

    public function testReturnsSortByForAttributeAndDirectionSpecifiedInQueryString(): void
    {
        $sortAttributeName = 'foo';
        $sortDirection = SortDirection::ASC;

        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_QUERY_PARAMETER_NAME, true],
            [ProductListingPageRequest::SORT_DIRECTION_QUERY_PARAMETER_NAME, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_QUERY_PARAMETER_NAME, $sortAttributeName],
            [ProductListingPageRequest::SORT_DIRECTION_QUERY_PARAMETER_NAME, $sortDirection],
        ]);

        $stubDefaultSortBy = $this->createMock(SortBy::class);
        $expectedSortBy = new SortBy(
            AttributeCode::fromString($sortAttributeName),
            SortDirection::create($sortDirection)
        );
        $availableSortBy = [$stubDefaultSortBy, $expectedSortBy];

        $result = $this->pageRequest->getSelectedSortBy($this->stubRequest, $stubDefaultSortBy, ...$availableSortBy);

        $this->assertEquals($expectedSortBy, $result);
    }

    public function testReturnsSortByForAttributeAndDirectionSpecifiedInCookie(): void
    {
        $sortAttributeName = 'foo';
        $sortDirection = SortDirection::ASC;

        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_QUERY_PARAMETER_NAME, false],
            [ProductListingPageRequest::SORT_DIRECTION_QUERY_PARAMETER_NAME, false],
        ]);

        $this->stubRequest->method('hasCookie')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_COOKIE_NAME, true],
            [ProductListingPageRequest::SORT_DIRECTION_COOKIE_NAME, true],
        ]);

        $this->stubRequest->method('getCookieValue')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_COOKIE_NAME, $sortAttributeName],
            [ProductListingPageRequest::SORT_DIRECTION_COOKIE_NAME, $sortDirection],
        ]);

        $stubDefaultSortBy = $this->createMock(SortBy::class);
        $expectedSortBy = new SortBy(
            AttributeCode::fromString($sortAttributeName),
            SortDirection::create($sortDirection)
        );
        $availableSortBy = [$stubDefaultSortBy, $expectedSortBy];

        $result = $this->pageRequest->getSelectedSortBy($this->stubRequest, $stubDefaultSortBy, ...$availableSortBy);

        $this->assertEquals($expectedSortBy, $result);
    }

    public function testProductsPerPageCookieIsSetIfCorrespondingQueryParameterIsPresent(): void
    {
        $selectedNumberOfProductsPerPage = 2;

        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductListingPageRequest::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingPageRequest::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME, (string) $selectedNumberOfProductsPerPage],
        ]);

        $this->pageRequest->processCookies($this->stubRequest);

        $this->assertCookieHasBeenSet(
            ProductListingPageRequest::PRODUCTS_PER_PAGE_COOKIE_NAME,
            $selectedNumberOfProductsPerPage,
            ProductListingPageRequest::PRODUCTS_PER_PAGE_COOKIE_TTL
        );
    }

    public function testSortOrderAndDirectionCookiesAreNotSetIfSortOrderQueryParametersIsNotAmongConfiguredSortOrders(): void
    {
        $sortAttributeName = 'foo';
        $sortDirection = SortDirection::ASC;

        $defaultSortOrderAttributeName = 'bar';

        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_QUERY_PARAMETER_NAME, $sortAttributeName],
            [ProductListingPageRequest::SORT_DIRECTION_QUERY_PARAMETER_NAME, $sortDirection],
        ]);

        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $stubAttributeCode->method('isEqualTo')
            ->willReturnCallback(function ($attributeName) use ($defaultSortOrderAttributeName) {
                return $attributeName === $defaultSortOrderAttributeName;
            });

        $this->pageRequest->processCookies($this->stubRequest);

        $this->assertCookieHasNotBeenSet(
            ProductListingPageRequest::SORT_ORDER_COOKIE_NAME,
            $sortAttributeName,
            ProductListingPageRequest::SORT_ORDER_COOKIE_TTL
        );
        $this->assertCookieHasNotBeenSet(
            ProductListingPageRequest::SORT_DIRECTION_COOKIE_NAME,
            $sortDirection,
            ProductListingPageRequest::SORT_DIRECTION_COOKIE_TTL
        );
    }

    public function testSetsSortOrderAndDirectionCookiesIfCorrespondingQueryParametersArePresent(): void
    {
        $sortAttributeName = 'foo';
        $sortDirection = SortDirection::ASC;

        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_QUERY_PARAMETER_NAME, true],
            [ProductListingPageRequest::SORT_DIRECTION_QUERY_PARAMETER_NAME, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_QUERY_PARAMETER_NAME, $sortAttributeName],
            [ProductListingPageRequest::SORT_DIRECTION_QUERY_PARAMETER_NAME, $sortDirection],
        ]);

        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $stubAttributeCode->method('isEqualTo')->with($sortAttributeName)->willReturn(true);

        $testAvailableSortBy = [
            new SortBy(AttributeCode::fromString($sortAttributeName), SortDirection::create($sortDirection))
        ];

        $this->pageRequest->processCookies($this->stubRequest, ...$testAvailableSortBy);

        $this->assertCookieHasBeenSet(
            ProductListingPageRequest::SORT_ORDER_COOKIE_NAME,
            $sortAttributeName,
            ProductListingPageRequest::SORT_ORDER_COOKIE_TTL
        );
        $this->assertCookieHasBeenSet(
            ProductListingPageRequest::SORT_DIRECTION_COOKIE_NAME,
            $sortDirection,
            ProductListingPageRequest::SORT_DIRECTION_COOKIE_TTL
        );
    }

    public function testItMapsRequestParametersToFacetFieldNames(): void
    {
        /** @var FacetFiltersToIncludeInResult|MockObject $stubFacetFiltersToIncludeInResult */
        $stubFacetFiltersToIncludeInResult = $this->createMock(FacetFiltersToIncludeInResult::class);
        $stubFacetFiltersToIncludeInResult->method('getAttributeCodeStrings')->willReturn(['price_with_tax']);

        $this->stubSearchFieldToRequestParamMap->method('getQueryParameterName')->willReturnMap([
            ['price_with_tax', 'price'],
        ]);

        $this->stubRequest->method('hasQueryParameter')->willReturnMap([['price', true]]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([['price', '10.00 to 19.99']]);

        $result = $this->pageRequest->getSelectedFilterValues($this->stubRequest, $stubFacetFiltersToIncludeInResult);

        $this->assertArrayHasKey('price_with_tax', $result);
        $this->assertTrue(in_array('10.00 to 19.99', $result['price_with_tax']));
    }

    public function testSortByWithMappedAttributeCodeIsReturned(): void
    {
        $originalAttributeCodeString = 'foo';
        $mappedAttributeCodeString = 'bar';

        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $stubAttributeCode->method('__toString')->willReturn($originalAttributeCodeString);

        $stubSortDirection = $this->createMock(SortDirection::class);

        /** @var SortBy|MockObject $stubSortBy */
        $stubSortBy = $this->createMock(SortBy::class);
        $stubSortBy->method('getAttributeCode')->willReturn($stubAttributeCode);
        $stubSortBy->method('getSelectedDirection')->willReturn($stubSortDirection);

        $this->stubSearchFieldToRequestParamMap->method('getSearchFieldName')->willReturnMap([
            [$originalAttributeCodeString, $mappedAttributeCodeString],
        ]);

        $result = $this->pageRequest->createSortByForRequest($stubSortBy);

        $this->assertEquals($mappedAttributeCodeString, $result->getAttributeCode());
    }

    public function testReturnsDefaultSortByIfQueryStringValuesAreNotAmongAvailableSortBy(): void
    {
        $sortAttributeName = 'foo';
        $sortDirection = SortDirection::ASC;

        $defaultSortOrderAttributeName = 'bar';

        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_QUERY_PARAMETER_NAME, $sortAttributeName],
            [ProductListingPageRequest::SORT_DIRECTION_QUERY_PARAMETER_NAME, $sortDirection],
        ]);

        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $stubAttributeCode->method('isEqualTo')
            ->willReturnCallback(function ($attributeName) use ($defaultSortOrderAttributeName) {
                return $attributeName === $defaultSortOrderAttributeName;
            });

        $stubDefaultSortBy = $this->createMock(SortBy::class);
        $availableSortBy = [$stubDefaultSortBy];

        $result = $this->pageRequest->getSelectedSortBy($this->stubRequest, $stubDefaultSortBy, ...$availableSortBy);

        $this->assertSame($stubDefaultSortBy, $result);
    }

    public function testReturnsDefaultSortByIfCookieValuesAreNotAmongAvailableSortBy(): void
    {
        $sortAttributeName = 'foo';
        $sortDirection = SortDirection::ASC;

        $defaultSortOrderAttributeName = 'bar';

        $this->stubRequest->method('hasCookie')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_COOKIE_NAME, true],
            [ProductListingPageRequest::SORT_DIRECTION_COOKIE_NAME, true],
        ]);

        $this->stubRequest->method('getCookieValue')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_COOKIE_NAME, $sortAttributeName],
            [ProductListingPageRequest::SORT_DIRECTION_COOKIE_NAME, $sortDirection],
        ]);

        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $stubAttributeCode->method('isEqualTo')
            ->willReturnCallback(function ($attributeName) use ($defaultSortOrderAttributeName) {
                return $attributeName === $defaultSortOrderAttributeName;
            });

        $stubDefaultSortBy = $this->createMock(SortBy::class);
        $availableSortBy = [$stubDefaultSortBy];

        $result = $this->pageRequest->getSelectedSortBy($this->stubRequest, $stubDefaultSortBy, ...$availableSortBy);

        $this->assertSame($stubDefaultSortBy, $result);
    }
}

/**
 * @param string $name
 * @param mixed $value
 * @param int $expire
 */
function setcookie(string $name, $value, int $expire): void
{
    ProductListingPageRequestTest::trackSetCookieCalls($name, $value, $expire);
}

function time() : int
{
    return 0;
}
