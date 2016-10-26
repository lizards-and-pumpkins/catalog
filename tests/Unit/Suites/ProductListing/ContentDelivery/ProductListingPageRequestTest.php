<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderDirection;
use LizardsAndPumpkins\ProductListing\Exception\NoSelectedSortOrderException;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\SearchFieldToRequestParamMap;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @covers \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageRequest
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductsPerPage
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderDirection
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 */
class ProductListingPageRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductsPerPage|MockObject
     */
    private $stubProductsPerPage;

    /**
     * @var SortOrderConfig|MockObject
     */
    private $stubSortOrderConfig;

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
        $this->stubSortOrderConfig = $this->createMock(SortOrderConfig::class);
        $class = SearchFieldToRequestParamMap::class;
        $this->stubSearchFieldToRequestParamMap = $this->createMock($class);
        $this->pageRequest = new ProductListingPageRequest(
            $this->stubProductsPerPage,
            $this->stubSearchFieldToRequestParamMap,
            $this->stubSortOrderConfig
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

    public function testExceptionIsThrownIfNoSortOrderConfigIsSpecified()
    {
        $this->expectException(NoSelectedSortOrderException::class);
        $this->pageRequest->getSelectedSortOrderConfig($this->stubRequest);
    }

    public function testInitialSelectedSortOrderConfigIsReturned()
    {
        $this->stubSortOrderConfig->method('isSelected')->willReturn(true);
        $result = $this->pageRequest->getSelectedSortOrderConfig($this->stubRequest);
        $this->assertSame($this->stubSortOrderConfig, $result);
    }

    public function testSelectedSortOrderConfigForAttributeAndDirectionSpecifiedInQueryStringIsReturned()
    {
        $sortOrderAttributeName = 'foo';
        $sortOrderDirection = SortOrderDirection::ASC;

        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_QUERY_PARAMETER_NAME, $sortOrderAttributeName],
            [ProductListingPageRequest::SORT_DIRECTION_QUERY_PARAMETER_NAME, $sortOrderDirection],
        ]);

        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $stubAttributeCode->method('isEqualTo')->with($sortOrderAttributeName)->willReturn(true);

        $this->stubSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCode);

        $result = $this->pageRequest->getSelectedSortOrderConfig($this->stubRequest);

        $this->assertTrue($result->isSelected());
        $this->assertSame($sortOrderAttributeName, (string) $result->getAttributeCode());
        $this->assertSame($sortOrderDirection, (string) $result->getSelectedDirection());
    }

    public function testSelectedSortOrderConfigForAttributeAndDirectionSpecifiedInCookieIsReturned()
    {
        $sortOrderAttributeName = 'foo';
        $sortOrderDirection = SortOrderDirection::ASC;

        $this->stubRequest->method('hasCookie')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_COOKIE_NAME, true],
            [ProductListingPageRequest::SORT_DIRECTION_COOKIE_NAME, true],
        ]);

        $this->stubRequest->method('getCookieValue')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_COOKIE_NAME, $sortOrderAttributeName],
            [ProductListingPageRequest::SORT_DIRECTION_COOKIE_NAME, $sortOrderDirection],
        ]);

        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $stubAttributeCode->method('isEqualTo')->with($sortOrderAttributeName)->willReturn(true);

        $this->stubSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCode);

        $result = $this->pageRequest->getSelectedSortOrderConfig($this->stubRequest);

        $this->assertTrue($result->isSelected());
        $this->assertSame($sortOrderAttributeName, (string) $result->getAttributeCode());
        $this->assertSame($sortOrderDirection, (string) $result->getSelectedDirection());
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
        $sortOrderAttributeName = 'foo';
        $sortOrderDirection = SortOrderDirection::ASC;

        $defaultSortOrderAttributeName = 'bar';

        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_QUERY_PARAMETER_NAME, $sortOrderAttributeName],
            [ProductListingPageRequest::SORT_DIRECTION_QUERY_PARAMETER_NAME, $sortOrderDirection],
        ]);

        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $stubAttributeCode->method('isEqualTo')
            ->willReturnCallback(function ($attributeName) use ($defaultSortOrderAttributeName) {
                return $attributeName === $defaultSortOrderAttributeName;
            });

        $this->stubSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCode);

        $this->pageRequest->processCookies($this->stubRequest);

        $this->assertCookieHasNotBeenSet(
            ProductListingPageRequest::SORT_ORDER_COOKIE_NAME,
            $sortOrderAttributeName,
            ProductListingPageRequest::SORT_ORDER_COOKIE_TTL
        );
        $this->assertCookieHasNotBeenSet(
            ProductListingPageRequest::SORT_DIRECTION_COOKIE_NAME,
            $sortOrderDirection,
            ProductListingPageRequest::SORT_DIRECTION_COOKIE_TTL
        );
    }

    public function testSortOrderAndDirectionCookiesAreSetIfCorrespondingQueryParametersArePresent()
    {
        $sortOrderAttributeName = 'foo';
        $sortOrderDirection = SortOrderDirection::ASC;

        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_QUERY_PARAMETER_NAME, $sortOrderAttributeName],
            [ProductListingPageRequest::SORT_DIRECTION_QUERY_PARAMETER_NAME, $sortOrderDirection],
        ]);

        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $stubAttributeCode->method('isEqualTo')->with($sortOrderAttributeName)->willReturn(true);

        $this->stubSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCode);

        $this->pageRequest->processCookies($this->stubRequest);

        $this->assertCookieHasBeenSet(
            ProductListingPageRequest::SORT_ORDER_COOKIE_NAME,
            $sortOrderAttributeName,
            ProductListingPageRequest::SORT_ORDER_COOKIE_TTL
        );
        $this->assertCookieHasBeenSet(
            ProductListingPageRequest::SORT_DIRECTION_COOKIE_NAME,
            $sortOrderDirection,
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

    public function testSortOrderConfigWithMappedAttributeCodeIsReturned()
    {
        $originalAttributeCodeString = 'foo';
        $mappedAttributeCodeString = 'bar';

        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $stubAttributeCode->method('__toString')->willReturn($originalAttributeCodeString);

        $stubSortOrderDirection = $this->createMock(SortOrderDirection::class);

        /** @var SortOrderConfig|\PHPUnit_Framework_MockObject_MockObject $stubSortOrderConfig */
        $stubSortOrderConfig = $this->createMock(SortOrderConfig::class);
        $stubSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCode);
        $stubSortOrderConfig->method('getSelectedDirection')->willReturn($stubSortOrderDirection);

        $this->stubSearchFieldToRequestParamMap->method('getSearchFieldName')->willReturnMap([
            [$originalAttributeCodeString, $mappedAttributeCodeString],
        ]);

        $result = $this->pageRequest->createSortOrderConfigForRequest($stubSortOrderConfig);

        $this->assertEquals($mappedAttributeCodeString, $result->getAttributeCode());
    }

    public function testInitialSelectedSortOrderConfigIsReturnedIfQueryStringValuesAreNotAmongConfiguredSortOrders()
    {
        $sortOrderAttributeName = 'foo';
        $sortOrderDirection = SortOrderDirection::ASC;

        $defaultSortOrderAttributeName = 'bar';

        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_QUERY_PARAMETER_NAME, $sortOrderAttributeName],
            [ProductListingPageRequest::SORT_DIRECTION_QUERY_PARAMETER_NAME, $sortOrderDirection],
        ]);

        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $stubAttributeCode->method('isEqualTo')
            ->willReturnCallback(function ($attributeName) use ($defaultSortOrderAttributeName) {
                return $attributeName === $defaultSortOrderAttributeName;
            });

        $this->stubSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCode);
        $this->stubSortOrderConfig->method('isSelected')->willReturn(true);

        $result = $this->pageRequest->getSelectedSortOrderConfig($this->stubRequest);

        $this->assertSame($this->stubSortOrderConfig, $result);
    }

    public function testInitialSelectedSortOrderConfigIsReturnedIfCookieValuesAreNotAmongConfiguredSortOrders()
    {
        $sortOrderAttributeName = 'foo';
        $sortOrderDirection = SortOrderDirection::ASC;

        $defaultSortOrderAttributeName = 'bar';

        $this->stubRequest->method('hasCookie')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_COOKIE_NAME, true],
            [ProductListingPageRequest::SORT_DIRECTION_COOKIE_NAME, true],
        ]);

        $this->stubRequest->method('getCookieValue')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_COOKIE_NAME, $sortOrderAttributeName],
            [ProductListingPageRequest::SORT_DIRECTION_COOKIE_NAME, $sortOrderDirection],
        ]);

        $stubAttributeCode = $this->createMock(AttributeCode::class);
        $stubAttributeCode->method('isEqualTo')
            ->willReturnCallback(function ($attributeName) use ($defaultSortOrderAttributeName) {
                return $attributeName === $defaultSortOrderAttributeName;
            });

        $this->stubSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCode);
        $this->stubSortOrderConfig->method('isSelected')->willReturn(true);

        $result = $this->pageRequest->getSelectedSortOrderConfig($this->stubRequest);

        $this->assertSame($this->stubSortOrderConfig, $result);
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
