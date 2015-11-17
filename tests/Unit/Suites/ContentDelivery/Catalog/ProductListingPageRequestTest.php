<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\Catalog\Exception\NoSelectedSortOrderException;
use LizardsAndPumpkins\Http\HttpRequest;

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
     * @var ProductsPerPage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductsPerPage;

    /**
     * @var SortOrderConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSortOrderConfig;

    /**
     * @var ProductListingPageRequest
     */
    private $pageRequest;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    private function assertCookieHasBeenSet($name, $value, $ttl)
    {
        $cookieFormat = 'Set-Cookie: %s=%s; expires=%s; Max-Age=%s';

        $expectedHeader = sprintf($cookieFormat, $name, $value, gmdate('D, d-M-Y H:i:s T', time() + $ttl), $ttl);
        $expectedHeader1 = sprintf($cookieFormat, $name, $value, gmdate('D, d-M-Y H:i:s T', time() + $ttl - 1), $ttl);

        $headers = xdebug_get_headers();

        $this->assertTrue(in_array($expectedHeader, $headers) || in_array($expectedHeader1, $headers));
    }

    protected function setUp()
    {
        $this->stubProductsPerPage = $this->getMock(ProductsPerPage::class, [], [], '', false);
        $this->stubSortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);
        $this->pageRequest = new ProductListingPageRequest($this->stubProductsPerPage, $this->stubSortOrderConfig);
        $this->stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
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
        $filterAName = 'foo';
        $filterBName = 'bar';

        $testFilterNavigationConfig = [$filterAName => [], $filterBName => []];
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [$filterAName, 'baz,qux'],
            [$filterBName, null]
        ]);

        $result = $this->pageRequest->getSelectedFilterValues($this->stubRequest, $testFilterNavigationConfig);
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
        $this->setExpectedException(NoSelectedSortOrderException::class);
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

        $result = $this->pageRequest->getSelectedSortOrderConfig($this->stubRequest);

        $this->assertTrue($result->isSelected());
        $this->assertSame($sortOrderAttributeName, (string) $result->getAttributeCode());
        $this->assertSame($sortOrderDirection, $result->getSelectedDirection()->getDirection());
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

        $result = $this->pageRequest->getSelectedSortOrderConfig($this->stubRequest);

        $this->assertTrue($result->isSelected());
        $this->assertSame($sortOrderAttributeName, (string) $result->getAttributeCode());
        $this->assertSame($sortOrderDirection, $result->getSelectedDirection()->getDirection());
    }

    /**
     * @runInSeparateProcess
     * @requires extension xdebug
     */
    public function testProductsPerPageCookieIsSetIfCorrespondingQueryParameterIsPresent()
    {
        $selectedNumberOfProductsPerPage = 2;

        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingPageRequest::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME, $selectedNumberOfProductsPerPage]
        ]);

        $this->pageRequest->processCookies($this->stubRequest);

        $this->assertCookieHasBeenSet(
            ProductListingPageRequest::PRODUCTS_PER_PAGE_COOKIE_NAME,
            $selectedNumberOfProductsPerPage,
            ProductListingPageRequest::PRODUCTS_PER_PAGE_COOKIE_TTL
        );
    }

    /**
     * @runInSeparateProcess
     * @requires extension xdebug
     */
    public function testSortOrderCookieIsSetIfCorrespondingQueryParameterIsPresent()
    {
        $sortOrderAttributeName = 'foo';

        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingPageRequest::SORT_ORDER_QUERY_PARAMETER_NAME, $sortOrderAttributeName]
        ]);

        $this->pageRequest->processCookies($this->stubRequest);

        $this->assertCookieHasBeenSet(
            ProductListingPageRequest::SORT_ORDER_COOKIE_NAME,
            $sortOrderAttributeName,
            ProductListingPageRequest::SORT_DIRECTION_COOKIE_TTL
        );
    }

    /**
     * @runInSeparateProcess
     * @requires extension xdebug
     */
    public function testSortOrderDirectionCookieIsSetIfCorrespondingQueryParameterIsPresent()
    {
        $sortOrderDirection = SortOrderDirection::ASC;

        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingPageRequest::SORT_DIRECTION_QUERY_PARAMETER_NAME, $sortOrderDirection]
        ]);

        $this->pageRequest->processCookies($this->stubRequest);

        $this->assertCookieHasBeenSet(
            ProductListingPageRequest::SORT_DIRECTION_COOKIE_NAME,
            $sortOrderDirection,
            ProductListingPageRequest::SORT_DIRECTION_COOKIE_TTL
        );
    }
}
