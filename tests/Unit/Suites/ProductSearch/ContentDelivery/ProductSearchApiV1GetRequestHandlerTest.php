<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionAnything;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineConfiguration;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\Exception\UnableToProcessProductSearchRequestException;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\Exception\UnsupportedSortOrderException;
use LizardsAndPumpkins\ProductSearch\Exception\InvalidNumberOfProductsPerPageException;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineConfiguration
 * @uses   \LizardsAndPumpkins\Http\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\ProductSearch\ContentDelivery\DefaultFullTextCriteriaBuilder
 * @uses   \LizardsAndPumpkins\ProductSearch\QueryOptions
 */
class ProductSearchApiV1GetRequestHandlerTest extends TestCase
{
    /**
     * @var ProductSearchService
     */
    private $mockProductSearchService;

    /**
     * @var FullTextCriteriaBuilder
     */
    private $stubFullTextCriteriaBuilder;

    /**
     * @var ContextBuilder
     */
    private $stubContextBuilder;

    /**
     * @var SelectedFiltersParser
     */
    private $stubSelectedFiltersParser;

    /**
     * @var int
     */
    private $testDefaultNumberOfProductPerPage = 10;

    /**
     * @var int
     */
    private $testMaxAllowedProductsPerPage = 10;

    /**
     * @var SortBy
     */
    private $stubDefaultSorBy;

    /**
     * @var string[]
     */
    private $testSortableAttributeCodes = ['foo', 'bar'];

    /**
     * @var ProductSearchApiV1GetRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest
     */
    private $stubRequest;

    /**
     * @var CriteriaParser
     */
    private $stubCriteriaParser;

    /**
     * @var UrlToWebsiteMap
     */
    private $stubUrlToWebsiteMap;

    final protected function setUp(): void
    {
        $this->mockProductSearchService = $this->createMock(ProductSearchService::class);
        $this->stubContextBuilder = $this->createMock(ContextBuilder::class);
        $this->stubFullTextCriteriaBuilder = $this->createMock(FullTextCriteriaBuilder::class);
        $this->stubSelectedFiltersParser = $this->createMock(SelectedFiltersParser::class);
        $this->stubCriteriaParser = $this->createMock(CriteriaParser::class);

        $this->stubDefaultSorBy = $this->createMock(SortBy::class);
        $this->stubDefaultSorBy->method('getAttributeCode')->willReturn(AttributeCode::fromString('bar'));

        $stubSearchEngineConfiguration = new SearchEngineConfiguration(
            $this->testDefaultNumberOfProductPerPage,
            $this->testMaxAllowedProductsPerPage,
            $this->stubDefaultSorBy,
            ...$this->testSortableAttributeCodes
        );
        
        $this->stubUrlToWebsiteMap = $this->createMock(UrlToWebsiteMap::class);

        $this->requestHandler = new ProductSearchApiV1GetRequestHandler(
            $this->mockProductSearchService,
            $this->stubContextBuilder,
            $this->stubUrlToWebsiteMap,
            $this->stubFullTextCriteriaBuilder,
            $this->stubSelectedFiltersParser,
            $this->stubCriteriaParser,
            $stubSearchEngineConfiguration
        );

        $this->stubRequest = $this->createMock(HttpRequest::class);
    }

    public function testIsHttpRequestHandler(): void
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    /**
     * @dataProvider nonGetRequestMethodProvider
     * @param string $nonGetRequestMethod
     */
    public function testCanNotProcessNonHttpGetRequestTypes(string $nonGetRequestMethod): void
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
     * @param string $nonMatchingRequestPath
     */
    public function testCanNotProcessNonMatchingGetRequests(string $nonMatchingRequestPath): void
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn($nonMatchingRequestPath);
        $message = sprintf('GET request to "%s" should NOT be able to be processed', $nonMatchingRequestPath);
        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest), $message);
    }

    public function nonMatchingRequestPathProvider() : array
    {
        return [
            ['api/foo/'],
            ['api/products/'],
            ['api/products/foo/'],
        ];
    }

    /**
     * @dataProvider emptyStringProvider
     * @param string $emptyQuery
     */
    public function testCanNotProcessEmptyQuery(string $emptyQuery): void
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true]
        ]);
        $this->stubRequest->method('getQueryParameter')->with(ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER)
            ->willReturn($emptyQuery);

        $message = 'Request without query parameter should NOT be able to be processed';
        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest), $message);
    }

    public function emptyStringProvider() : array
    {
        return [[''], [' '], ["\n"], ["\t"], ["\r"], ["\0"], ["\x0B"], [" \n\t"]];
    }

    public function testCanProcessMatchingRequest(): void
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true]
        ]);
        $this->stubRequest->method('getQueryParameter')->with(ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER)
            ->willReturn('foo');

        $this->assertTrue($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testRequestsAllProductsIfNeitherQueryStringNorInitialCriteriaParameterIsSet(): void
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, false],
            [ProductSearchApiV1GetRequestHandler::INITIAL_CRITERIA_PARAMETER, false],
        ]);

        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($this->isInstanceOf(SearchCriterionAnything::class));

        $this->requestHandler->process($this->stubRequest);
    }

    public function testRequestsProductsMatchingStringIfQueryParameterIsPresent(): void
    {
        $queryString = 'foo';

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true]
        ]);
        $this->stubRequest->method('getQueryParameter')->with(ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER)
            ->willReturn($queryString);

        $dummySearchCriteria = $this->createMock(SearchCriteria::class);
        $this->stubFullTextCriteriaBuilder->method('createFromString')->with($queryString)
            ->willReturn($dummySearchCriteria);

        $this->mockProductSearchService->expects($this->once())->method('query')->with($dummySearchCriteria);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testReturnHttpResponse(): void
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
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

    public function testThrowsAnExceptionDuringAttemptToProcessInvalidRequest(): void
    {
        $this->expectException(UnableToProcessProductSearchRequestException::class);
        $this->expectExceptionMessage('Invalid product search API request.');

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_POST);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');

        $this->requestHandler->process($this->stubRequest);
    }

    public function testDelegatesFetchingProductsToTheProductSearchService(): void
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
            [ProductSearchApiV1GetRequestHandler::SORT_ORDER_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, 'foo'],
            [ProductSearchApiV1GetRequestHandler::SORT_ORDER_PARAMETER, 'bar'],
        ]);

        $stubProductSearchResult = $this->createMock(ProductSearchResult::class);
        $this->mockProductSearchService->expects($this->once())->method('query')->willReturn($stubProductSearchResult);

        $stubContext = $this->createMock(Context::class);
        $this->stubContextBuilder->method('createFromRequest')->with($this->stubRequest)->willReturn($stubContext);

        $response = $this->requestHandler->process($this->stubRequest);

        $this->assertSame(json_encode($stubProductSearchResult), $response->getBody());
        $this->assertSame(HttpResponse::STATUS_OK, $response->getStatusCode());
    }

    public function testDefaultValuesArePassedToProductSearchServiceIfNotSpecifiedExplicitly(): void
    {
        $queryString = 'foo';

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, $queryString],
        ]);

        $stubContext = $this->createMock(Context::class);
        $this->stubContextBuilder->method('createFromRequest')->with($this->stubRequest)->willReturn($stubContext);

        $expectedQueryOptions = QueryOptions::create(
            $filterSelection = [],
            $stubContext,
            new FacetFiltersToIncludeInResult(),
            $this->testDefaultNumberOfProductPerPage,
            $pageNumber = 0,
            $this->stubDefaultSorBy
        );

        $dummySearchCriteria = $this->createMock(SearchCriteria::class);
        $this->stubFullTextCriteriaBuilder->method('createFromString')->with($queryString)
            ->willReturn($dummySearchCriteria);

        $stubProductSearchResult = $this->createMock(ProductSearchResult::class);
        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($dummySearchCriteria, $expectedQueryOptions)
            ->willReturn($stubProductSearchResult);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testPassesRequestedNumberOfProductPerPageToProductSearchService(): void
    {
        $queryString = 'foo';
        $numberOfProductsPerPage = '5';

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
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

        $dummySearchCriteria = $this->createMock(SearchCriteria::class);
        $this->stubFullTextCriteriaBuilder->method('createFromString')->with($queryString)
            ->willReturn($dummySearchCriteria);

        $stubProductSearchResult = $this->createMock(ProductSearchResult::class);
        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($dummySearchCriteria, $expectedQueryOptions)
            ->willReturn($stubProductSearchResult);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testPassesRequestedPageNumberToProductSearchService(): void
    {
        $queryString = 'foo';
        $pageNumber = '1';

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
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
            $this->testDefaultNumberOfProductPerPage,
            (int) $pageNumber,
            $this->stubDefaultSorBy
        );

        $dummySearchCriteria = $this->createMock(SearchCriteria::class);
        $this->stubFullTextCriteriaBuilder->method('createFromString')->with($queryString)
            ->willReturn($dummySearchCriteria);

        $stubProductSearchResult = $this->createMock(ProductSearchResult::class);
        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($dummySearchCriteria, $expectedQueryOptions)
            ->willReturn($stubProductSearchResult);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testPassesRequestedSortOrderToProductSearchService(): void
    {
        $queryString = 'foo';
        $sortOrder = 'bar';

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
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
            $this->testDefaultNumberOfProductPerPage,
            $pageNumber = 0,
            $expectedSortBy
        );

        $dummySearchCriteria = $this->createMock(SearchCriteria::class);
        $this->stubFullTextCriteriaBuilder->method('createFromString')->with($queryString)
            ->willReturn($dummySearchCriteria);

        $stubProductSearchResult = $this->createMock(ProductSearchResult::class);
        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($dummySearchCriteria, $expectedQueryOptions)
            ->willReturn($stubProductSearchResult);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testIgnoresRequestedSortDirectionIfNoSortOrderIsSpecified(): void
    {
        $queryString = 'foo';

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
            [ProductSearchApiV1GetRequestHandler::SORT_DIRECTION_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, $queryString],
            [ProductSearchApiV1GetRequestHandler::SORT_DIRECTION_PARAMETER, 'desc'],
        ]);

        $stubContext = $this->createMock(Context::class);
        $this->stubContextBuilder->method('createFromRequest')->with($this->stubRequest)->willReturn($stubContext);

        $expectedQueryOptions = QueryOptions::create(
            $filterSelection = [],
            $stubContext,
            new FacetFiltersToIncludeInResult(),
            $this->testDefaultNumberOfProductPerPage,
            $pageNumber = 0,
            $this->stubDefaultSorBy
        );

        $dummySearchCriteria = $this->createMock(SearchCriteria::class);
        $this->stubFullTextCriteriaBuilder->method('createFromString')->with($queryString)
            ->willReturn($dummySearchCriteria);

        $stubProductSearchResult = $this->createMock(ProductSearchResult::class);
        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($dummySearchCriteria, $expectedQueryOptions)
            ->willReturn($stubProductSearchResult);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testPassesRequestedSortOrderAndDirectionToProductSearchService(): void
    {
        $queryString = 'foo';
        $sortOrder = 'bar';
        $sortDirection = 'desc';

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
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
            $this->testDefaultNumberOfProductPerPage,
            $pageNumber = 0,
            $expectedSortBy
        );

        $dummySearchCriteria = $this->createMock(SearchCriteria::class);
        $this->stubFullTextCriteriaBuilder->method('createFromString')->with($queryString)
            ->willReturn($dummySearchCriteria);

        $stubProductSearchResult = $this->createMock(ProductSearchResult::class);
        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($dummySearchCriteria, $expectedQueryOptions)
            ->willReturn($stubProductSearchResult);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testThrowsAnExceptionIfRequestedSortOrderIsNotAllowed(): void
    {
        $unsupportedSortAttributeCode = 'baz';

        $this->expectException(UnsupportedSortOrderException::class);
        $this->expectExceptionMessage(sprintf('Sorting by "%s" is not supported', $unsupportedSortAttributeCode));

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
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

    public function testThrowsAnExceptionIfRequestedNumberOfProductsIsHigherThanAllowed(): void
    {
        $rowsPerPage = $this->testMaxAllowedProductsPerPage + 1;

        $this->expectException(InvalidNumberOfProductsPerPageException::class);
        $this->expectExceptionMessage(sprintf(
            'Maximum allowed number of products per page is %d, got %d.',
            $this->testMaxAllowedProductsPerPage,
            $rowsPerPage
        ));

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
            [ProductSearchApiV1GetRequestHandler::NUMBER_OF_PRODUCTS_PER_PAGE_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, 'foo'],
            [ProductSearchApiV1GetRequestHandler::NUMBER_OF_PRODUCTS_PER_PAGE_PARAMETER, (string) $rowsPerPage],
        ]);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testAddsEmptySelectedFiltersArrayToQueryOptionsIfNoParameterIsPresent(): void
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::SELECTED_FILTERS_PARAMETER, false],
        ]);

        $stubContext = $this->createMock(Context::class);
        $this->stubContextBuilder->method('createFromRequest')->with($this->stubRequest)->willReturn($stubContext);

        $expectedQueryOptions = QueryOptions::create(
            $filterSelection = [],
            $stubContext,
            new FacetFiltersToIncludeInResult(),
            $this->testDefaultNumberOfProductPerPage,
            $pageNumber = 0,
            $this->stubDefaultSorBy
        );

        $stubProductSearchResult = $this->createMock(ProductSearchResult::class);
        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($this->isInstanceOf(SearchCriterionAnything::class), $expectedQueryOptions)
            ->willReturn($stubProductSearchResult);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testAddsParsedSelectedFiltersArrayToQueryOptionsIfParameterIsPresent(): void
    {
        $encodedSelectedFiltersString = 'dummy selected filters string';
        $decodedSelectedFilters = ['dummy-filter-key' => ['dummy filter value']];

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::SELECTED_FILTERS_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::SELECTED_FILTERS_PARAMETER, $encodedSelectedFiltersString],
        ]);

        $stubContext = $this->createMock(Context::class);
        $this->stubContextBuilder->method('createFromRequest')->with($this->stubRequest)->willReturn($stubContext);

        $this->stubSelectedFiltersParser->method('parse')->with($encodedSelectedFiltersString)
            ->willReturn($decodedSelectedFilters);

        $expectedQueryOptions = QueryOptions::create(
            $filterSelection = $decodedSelectedFilters,
            $stubContext,
            new FacetFiltersToIncludeInResult(),
            $this->testDefaultNumberOfProductPerPage,
            $pageNumber = 0,
            $this->stubDefaultSorBy
        );

        $stubProductSearchResult = $this->createMock(ProductSearchResult::class);
        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($this->isInstanceOf(SearchCriterionAnything::class), $expectedQueryOptions)
            ->willReturn($stubProductSearchResult);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testRequestsProductsWithinInitialCriteriaIfParameterIsSpecified(): void
    {
        $encodedInitialCriteriaString = 'dummy initial criteria string';
        $stubInitialCriteria = $this->createMock(SearchCriteria::class);

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::INITIAL_CRITERIA_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::INITIAL_CRITERIA_PARAMETER, $encodedInitialCriteriaString],
        ]);

        $this->stubCriteriaParser->method('createCriteriaFromString')->with($encodedInitialCriteriaString)
            ->willReturn($stubInitialCriteria);

        $this->mockProductSearchService->expects($this->once())->method('query')->with($stubInitialCriteria);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testRequestsProductsWithinInitialCriteriaAndQueryStringIfBothParametersAreSpecified(): void
    {
        $queryString = 'foo';
        $encodedInitialCriteriaString = 'dummy initial criteria string';
        $stubInitialCriteria = $this->createMock(SearchCriteria::class);

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
            [ProductSearchApiV1GetRequestHandler::INITIAL_CRITERIA_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, $queryString],
            [ProductSearchApiV1GetRequestHandler::INITIAL_CRITERIA_PARAMETER, $encodedInitialCriteriaString],
        ]);

        $this->stubCriteriaParser->method('createCriteriaFromString')->with($encodedInitialCriteriaString)
            ->willReturn($stubInitialCriteria);

        $dummySearchCriteria = $this->createMock(SearchCriteria::class);
        $this->stubFullTextCriteriaBuilder->method('createFromString')->with($queryString)
            ->willReturn($dummySearchCriteria);

        $expectedCriteria = CompositeSearchCriterion::createAnd($dummySearchCriteria, $stubInitialCriteria);
        $this->mockProductSearchService->expects($this->once())->method('query')->with($expectedCriteria);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testRequestsFacets(): void
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, false],
            [ProductSearchApiV1GetRequestHandler::INITIAL_CRITERIA_PARAMETER, false],
            [ProductSearchApiV1GetRequestHandler::FACETS_PARAMETER, true]
        ]);

        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::FACETS_PARAMETER, 'foo,bar']
        ]);

        $stubContext = $this->createMock(Context::class);
        $this->stubContextBuilder->method('createFromRequest')->with($this->stubRequest)->willReturn($stubContext);

        $expectedFacetFiltersToIncludeInResult = new FacetFiltersToIncludeInResult(
            new FacetFilterRequestSimpleField(AttributeCode::fromString('foo')),
            new FacetFilterRequestSimpleField(AttributeCode::fromString('bar'))
        );

        $expectedQueryOptions = QueryOptions::create(
            $filterSelection = [],
            $stubContext,
            $expectedFacetFiltersToIncludeInResult,
            $this->testDefaultNumberOfProductPerPage,
            $pageNumber = 0,
            $this->stubDefaultSorBy
        );

        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($this->isInstanceOf(SearchCriterionAnything::class), $expectedQueryOptions);

        $this->requestHandler->process($this->stubRequest);
    }
}
