<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection;
use LizardsAndPumpkins\ProductListing\Exception\NoSelectedSortOrderException;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\SearchFieldToRequestParamMap;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @covers \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageRequest
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductsPerPage
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 */
class ProductListingPageRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductsPerPage|MockObject
     */
    private $stubProductsPerPage;

    /**
     * @var SortBy|MockObject
     */
    private $stubSortBy;

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
        $this->stubSortBy = $this->createMock(SortBy::class);
        $class = SearchFieldToRequestParamMap::class;
        $this->stubSearchFieldToRequestParamMap = $this->createMock($class);
        $this->pageRequest = new ProductListingPageRequest(
            $this->stubProductsPerPage,
            $this->stubSearchFieldToRequestParamMap,
            $this->stubSortBy
        );
        $this->stubRequest = $this->createMock(HttpRequest::class);
    }

    protected function tearDown()
    {
        self::$setCookieValues = [];
    }

    public function testCurrentPageIsZeroByDefault()
    {
        $this->assertSame(0, $this->pageRequest->getCurrentPageNumber($this->stubRequest));
    }

    public function testCurrentPageNumberIsReturned()
    {
        $pageNumber = 2;
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

        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [$filterAName, 'baz,qux'],
            [$filterBName, null],
        ]);

        $result = $this->pageRequest->getSelectedFilterValues($this->stubRequest, $stubFacetFilterRequest);
        $expectedFilterValues = ['foo' => ['baz', 'qux'], 'bar' => []];

        $this->assertSame($expectedFilterValues, $result);
    }

    public function testInitialProductsPerPageConfigurationIsReturned()
    {
        $this->assertSame($this->stubProductsPerPage, $this->pageRequest->getProductsPerPage($this->stubRequest));
    }

    public function testProductsPerPageSpecifiedInQueryStringIsReturned()
    {
        $selectedNumberOfProductsPerPage = 2;
        $availableNumbersOfProductsPerPage = [1, 2];

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

    public function testExceptionIsThrownIfNoSortByIsSpecified()
    {
        $this->expectException(NoSelectedSortOrderException::class);
        $this->pageRequest->getSelectedSortBy($this->stubRequest);
    }

    public function testInitialSelectedSortByIsReturned()
    {
        $this->stubSortBy->method('isSelected')->willReturn(true);
        $result = $this->pageRequest->getSelectedSortBy($this->stubRequest);
        $this->assertSame($this->stubSortBy, $result);
    }

    public function testSelectedSortByForAttributeAndDirectionSpecifiedInQueryStringIsReturned()
    {
        $sortAttributeName = 'foo';
        $sortDirection = SortDirection::ASC;

        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_QUERY_PARAMETER_NAME, $sortAttributeName],
            [ProductListingPageRequest::SORT_DIRECTION_QUERY_PARAMETER_NAME, $sortDirection],
        ]);

        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $stubAttributeCode->method('isEqualTo')->with($sortAttributeName)->willReturn(true);

        $this->stubSortBy->method('getAttributeCode')->willReturn($stubAttributeCode);

        $result = $this->pageRequest->getSelectedSortBy($this->stubRequest);

        $this->assertTrue($result->isSelected());
        $this->assertSame($sortAttributeName, (string) $result->getAttributeCode());
        $this->assertSame($sortDirection, (string) $result->getSelectedDirection());
    }

    public function testSelectedSortByForAttributeAndDirectionSpecifiedInCookieIsReturned()
    {
        $sortAttributeName = 'foo';
        $sortDirection = SortDirection::ASC;

        $this->stubRequest->method('hasCookie')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_COOKIE_NAME, true],
            [ProductListingPageRequest::SORT_DIRECTION_COOKIE_NAME, true],
        ]);

        $this->stubRequest->method('getCookieValue')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_COOKIE_NAME, $sortAttributeName],
            [ProductListingPageRequest::SORT_DIRECTION_COOKIE_NAME, $sortDirection],
        ]);

        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $stubAttributeCode->method('isEqualTo')->with($sortAttributeName)->willReturn(true);

        $this->stubSortBy->method('getAttributeCode')->willReturn($stubAttributeCode);

        $result = $this->pageRequest->getSelectedSortBy($this->stubRequest);

        $this->assertTrue($result->isSelected());
        $this->assertSame($sortAttributeName, (string) $result->getAttributeCode());
        $this->assertSame($sortDirection, (string) $result->getSelectedDirection());
    }

    public function testProductsPerPageCookieIsSetIfCorrespondingQueryParameterIsPresent()
    {
        $selectedNumberOfProductsPerPage = 2;

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

        $this->stubSortBy->method('getAttributeCode')->willReturn($stubAttributeCode);

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

    public function testSortOrderAndDirectionCookiesAreSetIfCorrespondingQueryParametersArePresent()
    {
        $sortAttributeName = 'foo';
        $sortDirection = SortDirection::ASC;

        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_QUERY_PARAMETER_NAME, $sortAttributeName],
            [ProductListingPageRequest::SORT_DIRECTION_QUERY_PARAMETER_NAME, $sortDirection],
        ]);

        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $stubAttributeCode->method('isEqualTo')->with($sortAttributeName)->willReturn(true);

        $this->stubSortBy->method('getAttributeCode')->willReturn($stubAttributeCode);

        $this->pageRequest->processCookies($this->stubRequest);

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

        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            ['price', '10.00 to 19.99'],
        ]);

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

    public function testInitialSelectedSortByIsReturnedIfQueryStringValuesAreNotAmongConfiguredSortOrders()
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

        $this->stubSortBy->method('getAttributeCode')->willReturn($stubAttributeCode);
        $this->stubSortBy->method('isSelected')->willReturn(true);

        $result = $this->pageRequest->getSelectedSortBy($this->stubRequest);

        $this->assertSame($this->stubSortBy, $result);
    }

    public function testInitialSelectedSortByIsReturnedIfCookieValuesAreNotAmongConfiguredSortOrders()
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

        $this->stubSortBy->method('getAttributeCode')->willReturn($stubAttributeCode);
        $this->stubSortBy->method('isSelected')->willReturn(true);

        $result = $this->pageRequest->getSelectedSortBy($this->stubRequest);

        $this->assertSame($this->stubSortBy, $result);
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
