<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\Exception\UnableToHandleRequestException;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\FullTextCriteriaBuilder;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchService;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchRequestHandler
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchResultMetaSnippetContent
 * @uses   \LizardsAndPumpkins\ProductSearch\QueryOptions
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class ProductSearchRequestHandlerTest extends TestCase
{
    /**
     * @var ProductListingPageRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductListingPageRequest;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDataPoolReader;

    /**
     * @var ProductListingRequestHandler
     */
    private $requestHandler;

    /**
     * @var string
     */
    private $testMetaInfoKey = 'stub-meta-info-key';

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @var UrlToWebsiteMap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubUrlToWebsiteMap;

    /**
     * @return DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubDataPoolReader() : DataPoolReader
    {
        $pageSnippetCodes = ['child-snippet1'];

        $testMetaInfoSnippetJson = json_encode(ProductSearchResultMetaSnippetContent::create(
            'root-snippet-code',
            $pageSnippetCodes,
            $containers = [],
            $pageSpecificData = []
        )->toArray());

        $stubSearchEngineResponse = $this->createMock(SearchEngineResponse::class);

        $stubDataPoolReader = $this->createMock(DataPoolReader::class);
        $stubDataPoolReader->method('getSearchResults')->willReturn($stubSearchEngineResponse);
        $stubDataPoolReader->method('getSnippet')->willReturnMap([[$this->testMetaInfoKey, $testMetaInfoSnippetJson]]);

        return $stubDataPoolReader;
    }

    /**
     * @return ProductListingPageRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProductListingPageRequest() : ProductListingPageRequest
    {
        $stubProductsPerPage = $this->createMock(ProductsPerPage::class);
        $stubProductsPerPage->method('getSelectedNumberOfProductsPerPage')->willReturn(1);

        $stubSortBy = $this->createMock(SortBy::class);

        $stubProductListingPageRequest = $this->createMock(ProductListingPageRequest::class);
        $stubProductListingPageRequest->method('getProductsPerPage')->willReturn($stubProductsPerPage);
        $stubProductListingPageRequest->method('getSelectedSortBy')->willReturn($stubSortBy);
        $stubProductListingPageRequest->method('getSelectedFilterValues')->willReturn([]);
        $stubProductListingPageRequest->method('getCurrentPageNumber')->willReturn(0);

        return $stubProductListingPageRequest;
    }

    /**
     * @return ProductListingPageContentBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProductListingPageContentBuilder() : ProductListingPageContentBuilder
    {
        $stubHttpResponse = $this->createMock(HttpResponse::class);
        $stubPageContentBuilder = $this->createMock(ProductListingPageContentBuilder::class);
        $stubPageContentBuilder->method('buildPageContent')->willReturn($stubHttpResponse);

        return $stubPageContentBuilder;
    }

    protected function setUp()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);

        $this->stubDataPoolReader = $this->createStubDataPoolReader();

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->testMetaInfoKey);

        /** @var FacetFiltersToIncludeInResult|\PHPUnit_Framework_MockObject_MockObject $stubFacetFilterRequest */
        $stubFacetFilterRequest = $this->createMock(FacetFiltersToIncludeInResult::class);

        $stubProductListingPageContentBuilder = $this->createStubProductListingPageContentBuilder();

        $this->mockProductListingPageRequest = $this->createStubProductListingPageRequest();

        /** @var FullTextCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject $stubFullTextCriteriaBuilder */
        $stubFullTextCriteriaBuilder = $this->createMock(FullTextCriteriaBuilder::class);

        /** @var ProductSearchService|\PHPUnit_Framework_MockObject_MockObject $stubProductSearchService */
        $stubProductSearchService = $this->createMock(ProductSearchService::class);

        /** @var SortBy|\PHPUnit_Framework_MockObject_MockObject $stubDefaultSortBy */
        $stubDefaultSortBy = $this->createMock(SortBy::class);

        $this->stubUrlToWebsiteMap = $this->createMock(UrlToWebsiteMap::class);

        $this->requestHandler = new ProductSearchRequestHandler(
            $stubContext,
            $this->stubDataPoolReader,
            $stubSnippetKeyGenerator,
            $stubFacetFilterRequest,
            $this->stubUrlToWebsiteMap,
            $stubProductListingPageContentBuilder,
            $this->mockProductListingPageRequest,
            $stubProductSearchService,
            $stubFullTextCriteriaBuilder,
            $stubDefaultSortBy
        );

        $this->stubRequest = $this->createMock(HttpRequest::class);
    }

    public function testRequestCanNotBeProcessedIfRequestUrlIsNotEqualToSearchPageUrl() : HttpRequest
    {
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn('foo');
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);

        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));

        return $this->stubRequest;
    }

    public function testRequestCanNotBeProcessedIfRequestMethodIsNotGet()
    {
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_POST);

        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testRequestCanNotBeProcessedIfQueryStringParameterIsNotPresent()
    {
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME, false],
        ]);

        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testRequestCanNotBeProcessedIfQueryStringIsEmpty()
    {
        $queryString = '';

        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME, $queryString],
        ]);

        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));
    }

    /**
     * @depends testRequestCanNotBeProcessedIfRequestUrlIsNotEqualToSearchPageUrl
     */
    public function testExceptionIsThrownDuringAttemptToProcessInvalidRequest(HttpRequest $stubHttpRequest)
    {
        $this->expectException(UnableToHandleRequestException::class);
        $this->requestHandler->process($stubHttpRequest);
    }

    public function testTrueIsReturnedIfRequestCanBeProcessed() : HttpRequest
    {
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME, true],
        ]);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME, 'foo'],
        ]);

        $this->assertTrue($this->requestHandler->canProcess($this->stubRequest));

        return $this->stubRequest;
    }

    /**
     * @depends testTrueIsReturnedIfRequestCanBeProcessed
     */
    public function testCookieProcessingIsTriggered(HttpRequest $stubRequest)
    {
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        
        $this->mockProductListingPageRequest->expects($this->once())->method('processCookies');
        $this->requestHandler->process($stubRequest);
    }

    /**
     * @depends testTrueIsReturnedIfRequestCanBeProcessed
     */
    public function testHttpResponseIsReturned(HttpRequest $stubRequest)
    {
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        
        $this->assertInstanceOf(HttpResponse::class, $this->requestHandler->process($stubRequest));
    }

    /**
     * @depends testTrueIsReturnedIfRequestCanBeProcessed
     */
    public function testSortByAttributeCodesAreMappedBeforePassedToSearchEngine(HttpRequest $stubRequest)
    {
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        
        $this->mockProductListingPageRequest->expects($this->once())->method('createSortByForRequest')
            ->willReturn($this->createMock(SortBy::class));

        $this->requestHandler->process($stubRequest);
    }
}
