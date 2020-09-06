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
     * @var ProductListingPageRequest|MockObject
     */
    private $mockProductListingPageRequest;

    /**
     * @var ProductListingRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest|MockObject
     */
    private $stubRequest;

    /**
     * @return ProductListingPageRequest|MockObject
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
     * @return ProductListingPageContentBuilder|MockObject
     */
    private function createStubProductListingPageContentBuilder(): ProductListingPageContentBuilder
    {
        $stubHttpResponse = $this->createMock(HttpResponse::class);
        $stubPageContentBuilder = $this->createMock(ProductListingPageContentBuilder::class);
        $stubPageContentBuilder->method('buildPageContent')->willReturn($stubHttpResponse);

        return $stubPageContentBuilder;
    }

    final protected function setUp(): void
    {
        /** @var Context|MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);

        /** @var FacetFiltersToIncludeInResult|MockObject $stubFacetFilterRequest */
        $stubFacetFilterRequest = $this->createMock(FacetFiltersToIncludeInResult::class);

        $stubProductListingPageContentBuilder = $this->createStubProductListingPageContentBuilder();

        $this->mockProductListingPageRequest = $this->createStubProductListingPageRequest();

        /** @var FullTextCriteriaBuilder|MockObject $stubFullTextCriteriaBuilder */
        $stubFullTextCriteriaBuilder = $this->createMock(FullTextCriteriaBuilder::class);

        /** @var ProductSearchService|MockObject $stubProductSearchService */
        $stubProductSearchService = $this->createMock(ProductSearchService::class);

        /** @var SortBy|MockObject $stubDefaultSortBy */
        $stubDefaultSortBy = $this->createMock(SortBy::class);

        $pageMeta = [
            ProductSearchResultMetaSnippetContent::KEY_HANDLER_CODE => ProductSearchRequestHandler::CODE,
            ProductSearchResultMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => 'foo',
            ProductSearchResultMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductSearchResultMetaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
            ProductSearchResultMetaSnippetContent::KEY_PAGE_SPECIFIC_DATA => [],
        ];

        $this->requestHandler = new ProductSearchRequestHandler(
            $stubContext,
            $stubFacetFilterRequest,
            $stubProductListingPageContentBuilder,
            $this->mockProductListingPageRequest,
            $stubProductSearchService,
            $stubFullTextCriteriaBuilder,
            $pageMeta,
            $stubDefaultSortBy
        );

        $this->stubRequest = $this->createMock(HttpRequest::class);
    }

    public function testRequestCanNotBeProcessedIfRequestMethodIsNotGet(): void
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_POST);

        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testRequestCanNotBeProcessedIfQueryStringParameterIsNotPresent(): void
    {
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('hasQueryParameter')->willReturnMap([
            [ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME, false],
        ]);

        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testRequestCanNotBeProcessedIfQueryStringIsEmpty(): void
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
    public function testExceptionIsThrownDuringAttemptToProcessInvalidRequest(): void
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
    public function testCookieProcessingIsTriggered(HttpRequest $stubRequest): void
    {
        $this->mockProductListingPageRequest->expects($this->once())->method('processCookies');
        $this->requestHandler->process($stubRequest);
    }

    /**
     * @depends testTrueIsReturnedIfRequestCanBeProcessed
     */
    public function testHttpResponseIsReturned(HttpRequest $stubRequest): void
    {
        $this->assertInstanceOf(HttpResponse::class, $this->requestHandler->process($stubRequest));
    }

    /**
     * @depends testTrueIsReturnedIfRequestCanBeProcessed
     */
    public function testSortByAttributeCodesAreMappedBeforePassedToSearchEngine(HttpRequest $stubRequest): void
    {
        $this->mockProductListingPageRequest->expects($this->once())->method('createSortByForRequest')
            ->willReturn($this->createMock(SortBy::class));

        $this->requestHandler->process($stubRequest);
    }
}
