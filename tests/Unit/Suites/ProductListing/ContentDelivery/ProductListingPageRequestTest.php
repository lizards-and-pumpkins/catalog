<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\SearchFieldToRequestParamMap;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

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
    private function assertCookieHasBeenSet(string $name, $value, int $ttl)
    {
        $this->assertContains([$name, $value, time() + $ttl], self::$setCookieValues);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param int $ttl
     */
    private function assertCookieHasNotBeenSet(string $name, $value, int $ttl)
    {
        $this->assertNotContains([$name, $value, time() + $ttl], self::$setCookieValues);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param int $expire
     */
    public static function trackSetCookieCalls(string $name, $value, int $expire)
    {
        self::$setCookieValues[] = [$name, $value, $expire];
    }

    protected function setUp()
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

    protected function tearDown()
    {
        self::$setCookieValues = [];
    }

    public function testCurrentPageIsZeroByDefault()
    {
        $this->stubRequest->method('hasQueryParameter')
            ->with(ProductListingPageRequest::PAGINATION_QUERY_PARAMETER_NAME)->willReturn(false);
        $this->assertSame(0, $this->pageRequest->getCurrentPageNumber($this->stubRequest));
    }

    public function testCurrentPageIsZeroIfNegativePageNumberIsRequested()
    {
        $pageNumber = -2;

        $this->stubRequest->method('hasQueryParameter')
            ->with(ProductListingPageRequest::PAGINATION_QUERY_PARAMETER_NAME)->willReturn(true);
        $this->stubRequest->method('getQueryParameter')
            ->with(ProductListingPageRequest::PAGINATION_QUERY_PARAMETER_NAME)->willReturn($pageNumber + 1);

        $this->assertSame(0, $this->pageRequest->getCurrentPageNumber($this->stubRequest));

    }

    public function testReturnsCurrentPageNumber()
    {
        $pageNumber = 2;

        $this->stubRequest->method('hasQueryParameter')
            ->with(ProductListingPageRequest::PAGINATION_QUERY_PARAMETER_NAME)->willReturn(true);
        $this->stubRequest->method('getQueryParameter')
            ->with(ProductListingPageRequest::PAGINATION_QUERY_PARAMETER_NAME)->willReturn($pageNumber + 1);

        $this->assertSame($pageNumber, $this->pageRequest->getCurrentPageNumber($this->stubRequest));
    }

    public function testSelectedFiltersArrayIsReturned()
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

    public function testInitialProductsPerPageConfigurationIsReturned()
    {
        $this->stubRequest->method('hasQueryParameter')
            ->with(ProductListingPageRequest::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME)->willReturn(false);

        $this->assertSame($this->stubProductsPerPage, $this->pageRequest->getProductsPerPage($this->stubRequest));
    }

    public function testProductsPerPageSpecifiedInQueryStringIsReturned()
    {
        $selectedNumberOfProductsPerPage = 2;
        $availableNumbersOfProductsPerPage = [1, 2];

        $this->stubRequest->method('hasQueryParameter')
            ->with(ProductListingPageRequest::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME)->willReturn(true);
        $this->stubRequest->method('getQueryParameter')
            ->with(ProductListingPageRequest::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME)
            ->willReturn($selectedNumberOfProductsPerPage);

        $this->stubProductsPerPage->method('getNumbersOfProductsPerPage')
            ->willReturn($availableNumbersOfProductsPerPage);

        $result = $this->pageRequest->getProductsPerPage($this->stubRequest);

        $this->assertEquals($availableNumbersOfProductsPerPage, $result->getNumbersOfProductsPerPage());
        $this->assertEquals($selectedNumberOfProductsPerPage, $result->getSelectedNumberOfProductsPerPage());
    }

    public function testProductsPerPageSpecifiedInCookieIsReturned()
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

    public function testReturnsDefaultSortBy()
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

    public function testReturnsSortByForAttributeAndDirectionSpecifiedInQueryString()
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

    public function testReturnsSortByForAttributeAndDirectionSpecifiedInCookie()
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

    public function testProductsPerPageCookieIsSetIfCorrespondingQueryParameterIsPresent()
    {
        $selectedNumberOfProductsPerPage = 2;

        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductListingPageRequest::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingPageRequest::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME, $selectedNumberOfProductsPerPage],
        ]);

        $this->pageRequest->processCookies($this->stubRequest);

        $this->assertCookieHasBeenSet(
            ProductListingPageRequest::PRODUCTS_PER_PAGE_COOKIE_NAME,
            $selectedNumberOfProductsPerPage,
            ProductListingPageRequest::PRODUCTS_PER_PAGE_COOKIE_TTL
        );
    }

    public function testSortOrderAndDirectionCookiesAreNotSetIfSortOrderQueryParametersIsNotAmongConfiguredSortOrders()
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

    public function testSetsSortOrderAndDirectionCookiesIfCorrespondingQueryParametersArePresent()
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

    public function testItMapsRequestParametersToFacetFieldNames()
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
        $this->assertContains('10.00 to 19.99', $result['price_with_tax']);
    }

    public function testSortByWithMappedAttributeCodeIsReturned()
    {
        $originalAttributeCodeString = 'foo';
        $mappedAttributeCodeString = 'bar';

        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $stubAttributeCode->method('__toString')->willReturn($originalAttributeCodeString);

        $stubSortDirection = $this->createMock(SortDirection::class);

        /** @var SortBy|\PHPUnit_Framework_MockObject_MockObject $stubSortBy */
        $stubSortBy = $this->createMock(SortBy::class);
        $stubSortBy->method('getAttributeCode')->willReturn($stubAttributeCode);
        $stubSortBy->method('getSelectedDirection')->willReturn($stubSortDirection);

        $this->stubSearchFieldToRequestParamMap->method('getSearchFieldName')->willReturnMap([
            [$originalAttributeCodeString, $mappedAttributeCodeString],
        ]);

        $result = $this->pageRequest->createSortByForRequest($stubSortBy);

        $this->assertEquals($mappedAttributeCodeString, $result->getAttributeCode());
    }

    public function testReturnsDefaultSortByIfQueryStringValuesAreNotAmongAvailableSortBy()
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

    public function testReturnsDefaultSortByIfCookieValuesAreNotAmongAvailableSortBy()
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
function setcookie(string $name, $value, int $expire)
{
    ProductListingPageRequestTest::trackSetCookieCalls($name, $value, $expire);
}

function time() : int
{
    return 0;
}
