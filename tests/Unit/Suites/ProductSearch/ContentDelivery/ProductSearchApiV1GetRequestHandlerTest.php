<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\Exception\UnableToProcessProductSearchRequestException;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\Exception\UnsupportedSortOrderException;
use LizardsAndPumpkins\ProductSearch\Exception\InvalidNumberOfProductsPerPageException;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\ProductSearch\QueryOptions
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandler
 */
class ProductSearchApiV1GetRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductSearchService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductSearchService;

    /**
     * @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextBuilder;

    /**
     * @var int
     */
    private $defaultNumberOfProductPerPage = 10;

    /**
     * @var int
     */
    private $maxAllowedProductsPerPage = 10;

    /**
     * @var SortBy|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDefaultSorBy;

    /**
     * @var string[]
     */
    private $sortableAttributeCodes = ['foo', 'bar'];

    /**
     * @var ProductSearchApiV1GetRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    private function createSortByWithAttributeCode(string $attributeCode) : SortBy
    {
        return new SortBy(AttributeCode::fromString($attributeCode), SortDirection::create(SortDirection::ASC));
    }

    final protected function setUp()
    {
        $this->mockProductSearchService = $this->createMock(ProductSearchService::class);
        $this->stubContextBuilder = $this->createMock(ContextBuilder::class);

        $this->stubDefaultSorBy = $this->createMock(SortBy::class);
        $this->stubDefaultSorBy->method('getAttributeCode')->willReturn(AttributeCode::fromString('bar'));

        $this->requestHandler = new ProductSearchApiV1GetRequestHandler(
            $this->mockProductSearchService,
            $this->stubContextBuilder,
            $this->defaultNumberOfProductPerPage,
            $this->maxAllowedProductsPerPage,
            $this->stubDefaultSorBy,
            ...$this->sortableAttributeCodes
        );

        $this->stubRequest = $this->createMock(HttpRequest::class);
    }

    public function testIsApiRequestHandler()
    {
        $this->assertInstanceOf(ApiRequestHandler::class, $this->requestHandler);
    }

    /**
     * @dataProvider nonGetRequestMethodProvider
     */
    public function testCanNotProcessNonHttpGetRequestTypes(string $nonGetRequestMethod)
    {
        $this->stubRequest->method('getMethod')->willReturn($nonGetRequestMethod);
        $message = sprintf('%s request should NOT be able to be processed', $nonGetRequestMethod);
        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest), $message);
    }

    /**
     * @return array[]
     */
    public function nonGetRequestMethodProvider() : array
    {
        return [
            [HttpRequest::METHOD_POST],
            [HttpRequest::METHOD_PUT],
            [HttpRequest::METHOD_DELETE],
            [HttpRequest::METHOD_HEAD],
        ];
    }

    /**
     * @dataProvider nonMatchingRequestPathProvider
     */
    public function testCanNotProcessNonMatchingGetRequests(string $nonMatchingRequestPath)
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn($nonMatchingRequestPath);
        $message = sprintf('GET request to "%s" should NOT be able to be processed', $nonMatchingRequestPath);
        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest), $message);
    }

    public function nonMatchingRequestPathProvider() : array
    {
        return [
            ['/api/foo/'],
            ['/api/products/'],
            ['/api/products/foo/'],
        ];
    }

    public function testCanNotProcessIfQueryParameterIsMissing()
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, false]
        ]);

        $message = 'Request without query parameter should NOT be able to be processed';
        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest), $message);
    }

    /**
     * @dataProvider emptyQueryStringProvider
     */
    public function testCanNotProcessEmptyQuery(string $emptyQuery)
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true]
        ]);
        $this->stubRequest->method('getQueryParameter')->with(ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER)
            ->willReturn($emptyQuery);

        $message = 'Request without query parameter should NOT be able to be processed';
        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest), $message);
    }

    public function emptyQueryStringProvider() : array
    {
        return [[''], [' '], ["\n"], ["\t"], ["\r"], ["\0"], ["\x0B"], [" \n\t"]];
    }

    public function testCanProcessMatchingRequest()
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true]
        ]);
        $this->stubRequest->method('getQueryParameter')->with(ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER)
            ->willReturn('foo');

        $this->assertTrue($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testReturnHttpResponse()
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
            [ProductSearchApiV1GetRequestHandler::SORT_ORDER_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, 'foo'],
            [ProductSearchApiV1GetRequestHandler::SORT_ORDER_PARAMETER, 'bar'],
        ]);

        $this->assertInstanceOf(HttpResponse::class, $this->requestHandler->process($this->stubRequest));
    }

    public function testThrowsAnExceptionDuringAttemptToProcessInvalidRequest()
    {
        $this->expectException(UnableToProcessProductSearchRequestException::class);

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_POST);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');

        $this->requestHandler->process($this->stubRequest);
    }

    public function testDelegatesFetchingProductsToTheProductSearchService()
    {
        $testProductData = ['total' => 1, 'data' => ['Dummy data']];

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
            [ProductSearchApiV1GetRequestHandler::SORT_ORDER_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, 'foo'],
            [ProductSearchApiV1GetRequestHandler::SORT_ORDER_PARAMETER, 'bar'],
        ]);

        $this->mockProductSearchService->expects($this->once())->method('query')->willReturn($testProductData);

        $stubContext = $this->createMock(Context::class);
        $this->stubContextBuilder->method('createFromRequest')->with($this->stubRequest)->willReturn($stubContext);

        $response = $this->requestHandler->process($this->stubRequest);

        $this->assertSame(json_encode($testProductData), $response->getBody());
        $this->assertSame(HttpResponse::STATUS_OK, $response->getStatusCode());
    }

    public function testDefaultValuesArePassedToProductSearchServiceIfNotSpecifiedExplicitly()
    {
        $queryString = 'foo';

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, 'foo'],
        ]);

        $stubContext = $this->createMock(Context::class);
        $this->stubContextBuilder->method('createFromRequest')->with($this->stubRequest)->willReturn($stubContext);

        $expectedQueryOptions = QueryOptions::create(
            $filterSelection = [],
            $stubContext,
            new FacetFiltersToIncludeInResult(),
            $this->defaultNumberOfProductPerPage,
            $pageNumber = 0,
            $this->stubDefaultSorBy
        );

        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($queryString, $expectedQueryOptions)->willReturn([]);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testPassesRequestedNumberOfProductPerPageToProductSearchService()
    {
        $queryString = 'foo';
        $numberOfProductsPerPage = '5';

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
            [ProductSearchApiV1GetRequestHandler::NUMBER_OF_PRODUCTS_PER_PAGE_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, $queryString],
            [ProductSearchApiV1GetRequestHandler::NUMBER_OF_PRODUCTS_PER_PAGE_PARAMETER, $numberOfProductsPerPage],
        ]);

        $stubContext = $this->createMock(Context::class);
        $this->stubContextBuilder->method('createFromRequest')->with($this->stubRequest)->willReturn($stubContext);

        $expectedQueryOptions = QueryOptions::create(
            $filterSelection = [],
            $stubContext,
            new FacetFiltersToIncludeInResult(),
            (int) $numberOfProductsPerPage,
            $pageNumber = 0,
            $this->stubDefaultSorBy
        );

        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($queryString, $expectedQueryOptions)->willReturn([]);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testPassesRequestedPageNumberToProductSearchService()
    {
        $queryString = 'foo';
        $pageNumber = '1';

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
            [ProductSearchApiV1GetRequestHandler::PAGE_NUMBER_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, $queryString],
            [ProductSearchApiV1GetRequestHandler::PAGE_NUMBER_PARAMETER, $pageNumber],
        ]);

        $stubContext = $this->createMock(Context::class);
        $this->stubContextBuilder->method('createFromRequest')->with($this->stubRequest)->willReturn($stubContext);

        $expectedQueryOptions = QueryOptions::create(
            $filterSelection = [],
            $stubContext,
            new FacetFiltersToIncludeInResult(),
            $this->defaultNumberOfProductPerPage,
            (int) $pageNumber,
            $this->stubDefaultSorBy
        );

        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($queryString, $expectedQueryOptions)->willReturn([]);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testPassesRequestedSortOrderToProductSearchService()
    {
        $queryString = 'foo';
        $sortOrder = 'bar';

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
            [ProductSearchApiV1GetRequestHandler::SORT_ORDER_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, $queryString],
            [ProductSearchApiV1GetRequestHandler::SORT_ORDER_PARAMETER, $sortOrder],
        ]);

        $stubContext = $this->createMock(Context::class);
        $this->stubContextBuilder->method('createFromRequest')->with($this->stubRequest)->willReturn($stubContext);

        $expectedSortBy = new SortBy(AttributeCode::fromString($sortOrder), SortDirection::create(SortDirection::ASC));

        $expectedQueryOptions = QueryOptions::create(
            $filterSelection = [],
            $stubContext,
            new FacetFiltersToIncludeInResult(),
            $this->defaultNumberOfProductPerPage,
            $pageNumber = 0,
            $expectedSortBy
        );

        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($queryString, $expectedQueryOptions)->willReturn([]);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testIgnoresRequestedSortDirectionIfNoSortOrderIsSpecified()
    {
        $queryString = 'foo';

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
            [ProductSearchApiV1GetRequestHandler::SORT_DIRECTION_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, 'foo'],
            [ProductSearchApiV1GetRequestHandler::SORT_DIRECTION_PARAMETER, 'desc'],
        ]);

        $stubContext = $this->createMock(Context::class);
        $this->stubContextBuilder->method('createFromRequest')->with($this->stubRequest)->willReturn($stubContext);

        $expectedQueryOptions = QueryOptions::create(
            $filterSelection = [],
            $stubContext,
            new FacetFiltersToIncludeInResult(),
            $this->defaultNumberOfProductPerPage,
            $pageNumber = 0,
            $this->stubDefaultSorBy
        );

        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($queryString, $expectedQueryOptions)->willReturn([]);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testPassesRequestedSortOrderAndDirectionToProductSearchService()
    {
        $queryString = 'foo';
        $sortOrder = 'bar';
        $sortDirection = 'desc';

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
            [ProductSearchApiV1GetRequestHandler::SORT_ORDER_PARAMETER, true],
            [ProductSearchApiV1GetRequestHandler::SORT_DIRECTION_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, $queryString],
            [ProductSearchApiV1GetRequestHandler::SORT_ORDER_PARAMETER, $sortOrder],
            [ProductSearchApiV1GetRequestHandler::SORT_DIRECTION_PARAMETER, $sortDirection],
        ]);

        $stubContext = $this->createMock(Context::class);
        $this->stubContextBuilder->method('createFromRequest')->with($this->stubRequest)->willReturn($stubContext);

        $expectedSortBy = new SortBy(AttributeCode::fromString($sortOrder), SortDirection::create($sortDirection));

        $expectedQueryOptions = QueryOptions::create(
            $filterSelection = [],
            $stubContext,
            new FacetFiltersToIncludeInResult(),
            $this->defaultNumberOfProductPerPage,
            $pageNumber = 0,
            $expectedSortBy
        );

        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($queryString, $expectedQueryOptions)->willReturn([]);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testThrowsAnExceptionIfRequestedSortOrderIsNotAllowed()
    {
        $unsupportedSortAttributeCode = 'baz';

        $this->expectException(UnsupportedSortOrderException::class);
        $this->expectExceptionMessage(sprintf('Sorting by "%s" is not supported', $unsupportedSortAttributeCode));

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
            [ProductSearchApiV1GetRequestHandler::SORT_ORDER_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, 'foo'],
            [ProductSearchApiV1GetRequestHandler::SORT_ORDER_PARAMETER, $unsupportedSortAttributeCode],
        ]);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testThrowsAnExceptionIfRequestedNumberOfProductsIsHigherThanAllowed()
    {
        $rowsPerPage = $this->maxAllowedProductsPerPage + 1;

        $this->expectException(InvalidNumberOfProductsPerPageException::class);
        $this->expectExceptionMessage(sprintf(
            'Maximum allowed number of products per page is %d, got %d.',
            $this->maxAllowedProductsPerPage,
            $rowsPerPage
        ));

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
            [ProductSearchApiV1GetRequestHandler::NUMBER_OF_PRODUCTS_PER_PAGE_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, 'foo'],
            [ProductSearchApiV1GetRequestHandler::NUMBER_OF_PRODUCTS_PER_PAGE_PARAMETER, $rowsPerPage],
        ]);

        $this->requestHandler->process($this->stubRequest);
    }
}
