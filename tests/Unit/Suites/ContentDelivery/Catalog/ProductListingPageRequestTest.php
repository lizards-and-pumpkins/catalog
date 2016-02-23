<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\Catalog\Exception\NoSelectedSortOrderException;
use LizardsAndPumpkins\ContentDelivery\Catalog\Search\SearchFieldToRequestParamMap;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Product\AttributeCode;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingPageRequest
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductsPerPage
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
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
    private function assertCookieHasBeenSet($name, $value, $ttl)
    {
        $this->assertContains([$name, $value, time() + $ttl], self::$setCookieValues);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param int $ttl
     */
    private function assertCookieHasNotBeenSet($name, $value, $ttl)
    {
        $this->assertNotContains([$name, $value, time() + $ttl], self::$setCookieValues);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @param int $expire
     */
    public static function trackSetCookieCalls($name, $value, $expire)
    {
        self::$setCookieValues[] = [$name, $value, $expire];
    }

    protected function setUp()
    {
        $this->stubProductsPerPage = $this->getMock(ProductsPerPage::class, [], [], '', false);
        $this->stubSortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);
        $class = SearchFieldToRequestParamMap::class;
        $this->stubSearchFieldToRequestParamMap = $this->getMock($class, [], [], '', false);
        $this->pageRequest = new ProductListingPageRequest(
            $this->stubProductsPerPage,
            $this->stubSearchFieldToRequestParamMap,
            $this->stubSortOrderConfig
        );
        $this->stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
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
        $stubFacetFilterRequest = $this->getMock(FacetFiltersToIncludeInResult::class, [], [], '', false);
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

        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
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

        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
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

        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
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

        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
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
        $stubFacetFiltersToIncludeInResult = $this->getMock(FacetFiltersToIncludeInResult::class, [], [], '', false);
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

        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
        $stubAttributeCode->method('__toString')->willReturn($originalAttributeCodeString);

        $stubSortOrderDirection = $this->getMock(SortOrderDirection::class, [], [], '', false);

        /** @var SortOrderConfig|\PHPUnit_Framework_MockObject_MockObject $stubSortOrderConfig */
        $stubSortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);
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

        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
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

        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
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
function setcookie($name, $value, $expire)
{
    ProductListingPageRequestTest::trackSetCookieCalls($name, $value, $expire);
}

/**
 * @return int
 */
function time()
{
    return 0;
}
