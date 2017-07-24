<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
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
     * @var ProductListingRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @return ProductListingPageRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProductListingPageRequest(): ProductListingPageRequest
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
    private function createStubProductListingPageContentBuilder(): ProductListingPageContentBuilder
    {
        $stubHttpResponse = $this->createMock(HttpResponse::class);
        $stubPageContentBuilder = $this->createMock(ProductListingPageContentBuilder::class);
        $stubPageContentBuilder->method('buildPageContent')->willReturn($stubHttpResponse);

        return $stubPageContentBuilder;
    }

    final protected function setUp()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);

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

        $metaJson = json_encode([
            ProductSearchResultMetaSnippetContent::KEY_HANDLER_CODE => ProductSearchRequestHandler::CODE,
            ProductSearchResultMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => 'foo',
            ProductSearchResultMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductSearchResultMetaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
            ProductSearchResultMetaSnippetContent::KEY_PAGE_SPECIFIC_DATA => [],
        ]);

        $this->requestHandler = new ProductSearchRequestHandler(
            $stubContext,
            $stubFacetFilterRequest,
            $stubProductListingPageContentBuilder,
            $this->mockProductListingPageRequest,
            $stubProductSearchService,
            $stubFullTextCriteriaBuilder,
            $metaJson,
            $stubDefaultSortBy
        );

        $this->stubRequest = $this->createMock(HttpRequest::class);
    }

    public function testRequestCanNotBeProcessedIfRequestMethodIsNotGet()
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_POST);

        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testRequestCanNotBeProcessedIfQueryStringParameterIsNotPresent()
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME, false],
        ]);

        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testRequestCanNotBeProcessedIfQueryStringIsEmpty()
    {
        $queryString = '';

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
     * @depends testRequestCanNotBeProcessedIfRequestMethodIsNotGet
     */
    public function testExceptionIsThrownDuringAttemptToProcessInvalidRequest()
    {
        $this->expectException(UnableToHandleRequestException::class);
        $this->requestHandler->process($this->stubRequest);
    }

    public function testTrueIsReturnedIfRequestCanBeProcessed(): HttpRequest
    {
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
        $this->mockProductListingPageRequest->expects($this->once())->method('processCookies');
        $this->requestHandler->process($stubRequest);
    }

    /**
     * @depends testTrueIsReturnedIfRequestCanBeProcessed
     */
    public function testHttpResponseIsReturned(HttpRequest $stubRequest)
    {
        $this->assertInstanceOf(HttpResponse::class, $this->requestHandler->process($stubRequest));
    }

    /**
     * @depends testTrueIsReturnedIfRequestCanBeProcessed
     */
    public function testSortByAttributeCodesAreMappedBeforePassedToSearchEngine(HttpRequest $stubRequest)
    {
        $this->mockProductListingPageRequest->expects($this->once())->method('createSortByForRequest')
            ->willReturn($this->createMock(SortBy::class));

        $this->requestHandler->process($stubRequest);
    }
}
