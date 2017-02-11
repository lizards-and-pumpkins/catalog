<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionAnything;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionFullText;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineConfiguration;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionFullText
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineConfiguration
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\ProductSearch\QueryOptions
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandler
 */
class ProductSearchApiV1GetRequestHandlerTest extends TestCase
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
     * @var SelectedFiltersParser|\PHPUnit_Framework_MockObject_MockObject
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
     * @var SortBy|\PHPUnit_Framework_MockObject_MockObject
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
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @var CriteriaParser|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubCriteriaParser;

    /**
     * @var SearchEngineConfiguration|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchEngineConfiguration;

    final protected function setUp()
    {
        $this->mockProductSearchService = $this->createMock(ProductSearchService::class);
        $this->stubContextBuilder = $this->createMock(ContextBuilder::class);
        $fullTextSearchTermCombinationOperator = CompositeSearchCriterion::OR_CONDITION;
        $this->stubSelectedFiltersParser = $this->createMock(SelectedFiltersParser::class);
        $this->stubCriteriaParser = $this->createMock(CriteriaParser::class);

        $this->stubDefaultSorBy = $this->createMock(SortBy::class);
        $this->stubDefaultSorBy->method('getAttributeCode')->willReturn(AttributeCode::fromString('bar'));

        $this->stubSearchEngineConfiguration = new SearchEngineConfiguration(
            $this->testDefaultNumberOfProductPerPage,
            $this->testMaxAllowedProductsPerPage,
            $this->stubDefaultSorBy,
            ...$this->testSortableAttributeCodes
        );

        $this->requestHandler = new ProductSearchApiV1GetRequestHandler(
            $this->mockProductSearchService,
            $this->stubContextBuilder,
            $fullTextSearchTermCombinationOperator,
            $this->stubSelectedFiltersParser,
            $this->stubCriteriaParser,
            $this->stubSearchEngineConfiguration
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

    /**
     * @dataProvider emptyStringProvider
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

    public function emptyStringProvider() : array
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

    /**
     * @dataProvider fullTextSearchTermCombinationOperatorProvider
     */
    public function testCreatesACombinedCriteriaIfQueryStringContainsOfMultipleWords(string $fullTextSearchCondition)
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true]
        ]);
        $this->stubRequest->method('getQueryParameter')->with(ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER)
            ->willReturn('foo bar');

        $expectedCriteria = CompositeSearchCriterion::create(
            $fullTextSearchCondition,
            new SearchCriterionFullText('foo'),
            new SearchCriterionFullText('bar')
        );

        $this->mockProductSearchService->expects($this->once())->method('query')->with($expectedCriteria);

        $requestHandler = new ProductSearchApiV1GetRequestHandler(
            $this->mockProductSearchService,
            $this->stubContextBuilder,
            $fullTextSearchCondition,
            $this->stubSelectedFiltersParser,
            $this->stubCriteriaParser,
            $this->stubSearchEngineConfiguration
        );

        $requestHandler->process($this->stubRequest);
    }

    public function fullTextSearchTermCombinationOperatorProvider(): array
    {
        return [
            [CompositeSearchCriterion::OR_CONDITION],
            [CompositeSearchCriterion::AND_CONDITION],
        ];
    }

    public function testRequestsAllProductsIfNeitherQueryStringNorInitialCriteriaParameterIsSet()
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, false],
            [ProductSearchApiV1GetRequestHandler::INITIAL_CRITERIA_PARAMETER, false],
        ]);

        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($this->isInstanceOf(SearchCriterionAnything::class));

        $this->requestHandler->process($this->stubRequest);
    }

    public function testRequestsProductsMatchingStringIfQueryParameterIsPresent()
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true]
        ]);
        $this->stubRequest->method('getQueryParameter')->with(ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER)
            ->willReturn('foo');

        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($this->isInstanceOf(SearchCriterionFullText::class));

        $this->requestHandler->process($this->stubRequest);
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
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_POST);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');

        $response = $this->requestHandler->process($this->stubRequest);
        $expectedResponseBody = json_encode(['error' => 'Invalid product search API request.']);

        $this->assertSame($expectedResponseBody, $response->getBody());
    }

    public function testDelegatesFetchingProductsToTheProductSearchService()
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

        $stubProductSearchResult = $this->createMock(ProductSearchResult::class);
        $this->mockProductSearchService->expects($this->once())->method('query')->willReturn($stubProductSearchResult);

        $stubContext = $this->createMock(Context::class);
        $this->stubContextBuilder->method('createFromRequest')->with($this->stubRequest)->willReturn($stubContext);

        $response = $this->requestHandler->process($this->stubRequest);

        $this->assertSame(json_encode($stubProductSearchResult), $response->getBody());
        $this->assertSame(HttpResponse::STATUS_OK, $response->getStatusCode());
    }

    public function testDefaultValuesArePassedToProductSearchServiceIfNotSpecifiedExplicitly()
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, 'foo'],
        ]);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
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
            ->with($this->isInstanceOf(SearchCriterionFullText::class), $expectedQueryOptions)
            ->willReturn($stubProductSearchResult);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testPassesRequestedNumberOfProductPerPageToProductSearchService()
    {
        $numberOfProductsPerPage = '5';

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
            [ProductSearchApiV1GetRequestHandler::NUMBER_OF_PRODUCTS_PER_PAGE_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, 'foo'],
            [ProductSearchApiV1GetRequestHandler::NUMBER_OF_PRODUCTS_PER_PAGE_PARAMETER, $numberOfProductsPerPage],
        ]);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
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

        $stubProductSearchResult = $this->createMock(ProductSearchResult::class);
        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($this->isInstanceOf(SearchCriterionFullText::class), $expectedQueryOptions)
            ->willReturn($stubProductSearchResult);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testPassesRequestedPageNumberToProductSearchService()
    {
        $pageNumber = '1';

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
            [ProductSearchApiV1GetRequestHandler::PAGE_NUMBER_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, 'foo'],
            [ProductSearchApiV1GetRequestHandler::PAGE_NUMBER_PARAMETER, $pageNumber],
        ]);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
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

        $stubProductSearchResult = $this->createMock(ProductSearchResult::class);
        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($this->isInstanceOf(SearchCriterionFullText::class), $expectedQueryOptions)
            ->willReturn($stubProductSearchResult);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testPassesRequestedSortOrderToProductSearchService()
    {
        $sortOrder = 'bar';

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
            [ProductSearchApiV1GetRequestHandler::SORT_ORDER_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, 'foo'],
            [ProductSearchApiV1GetRequestHandler::SORT_ORDER_PARAMETER, $sortOrder],
        ]);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
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

        $stubProductSearchResult = $this->createMock(ProductSearchResult::class);
        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($this->isInstanceOf(SearchCriterionFullText::class), $expectedQueryOptions)
            ->willReturn($stubProductSearchResult);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testIgnoresRequestedSortDirectionIfNoSortOrderIsSpecified()
    {
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

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
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
            ->with($this->isInstanceOf(SearchCriterionFullText::class), $expectedQueryOptions)
            ->willReturn($stubProductSearchResult);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testPassesRequestedSortOrderAndDirectionToProductSearchService()
    {
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
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, 'foo'],
            [ProductSearchApiV1GetRequestHandler::SORT_ORDER_PARAMETER, $sortOrder],
            [ProductSearchApiV1GetRequestHandler::SORT_DIRECTION_PARAMETER, $sortDirection],
        ]);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
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

        $stubProductSearchResult = $this->createMock(ProductSearchResult::class);
        $this->mockProductSearchService->expects($this->once())->method('query')
            ->with($this->isInstanceOf(SearchCriterionFullText::class), $expectedQueryOptions)
            ->willReturn($stubProductSearchResult);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testThrowsAnExceptionIfRequestedSortOrderIsNotAllowed()
    {
        $unsupportedSortAttributeCode = 'baz';

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

        $response = $this->requestHandler->process($this->stubRequest);
        $expectedResponseBody = json_encode(
            ['error' => sprintf('Sorting by "%s" is not supported', $unsupportedSortAttributeCode)]
        );

        $this->assertSame($expectedResponseBody, $response->getBody());
    }

    public function testThrowsAnExceptionIfRequestedNumberOfProductsIsHigherThanAllowed()
    {
        $rowsPerPage = $this->testMaxAllowedProductsPerPage + 1;

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

        $response = $this->requestHandler->process($this->stubRequest);
        $expectedResponseBody = json_encode(
            ['error' => sprintf(
                'Maximum allowed number of products per page is %d, got %d.',
                $this->testMaxAllowedProductsPerPage,
                $rowsPerPage
            )]
        );

        $this->assertSame($expectedResponseBody, $response->getBody());
    }

    public function testAddsEmptySelectedFiltersArrayToQueryOptionsIfNoParameterIsPresent()
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::SELECTED_FILTERS_PARAMETER, false],
        ]);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
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

    public function testAddsParsedSelectedFiltersArrayToQueryOptionsIfParameterIsPresent()
    {
        $encodedSelectedFiltersString = 'dummy selected filters string';
        $decodedSelectedFilters = ['dummy-filter-key' => ['dummy filter value']];

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::SELECTED_FILTERS_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::SELECTED_FILTERS_PARAMETER, $encodedSelectedFiltersString],
        ]);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
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

    public function testRequestsProductsWithinInitialCriteriaIfParameterIsSpecified()
    {
        $encodedInitialCriteriaString = 'dummy initial criteria string';
        $stubInitialCriteria = $this->createMock(SearchCriteria::class);

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
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

    public function testRequestsProductsWithinInitialCriteriaAndQueryStringIfBothParametersAreSpecified()
    {
        $dummyQueryString = 'foo';
        $encodedInitialCriteriaString = 'dummy initial criteria string';
        $stubInitialCriteria = $this->createMock(SearchCriteria::class);

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, true],
            [ProductSearchApiV1GetRequestHandler::INITIAL_CRITERIA_PARAMETER, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, $dummyQueryString],
            [ProductSearchApiV1GetRequestHandler::INITIAL_CRITERIA_PARAMETER, $encodedInitialCriteriaString],
        ]);

        $this->stubCriteriaParser->method('createCriteriaFromString')->with($encodedInitialCriteriaString)
            ->willReturn($stubInitialCriteria);

        $expectedCriteria = CompositeSearchCriterion::createAnd(
            new SearchCriterionFullText($dummyQueryString),
            $stubInitialCriteria
        );
        $this->mockProductSearchService->expects($this->once())->method('query')->with($expectedCriteria);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testRequestsFacets()
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getPathWithoutWebsitePrefix')->willReturn('/api/product');
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::QUERY_PARAMETER, false],
            [ProductSearchApiV1GetRequestHandler::INITIAL_CRITERIA_PARAMETER, false],
            [ProductSearchApiV1GetRequestHandler::FACETS_PARAMETER, true]
        ]);

        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchApiV1GetRequestHandler::FACETS_PARAMETER, 'foo,bar']
        ]);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
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
