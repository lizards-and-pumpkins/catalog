<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionAnything;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\ProductListing\Import\ProductListingMetaSnippetContent;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchService;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingRequestHandler
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingMetaSnippetContent
 * @uses   \LizardsAndPumpkins\ProductSearch\QueryOptions
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionAnything
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class ProductListingRequestHandlerTest extends TestCase
{
    /**
     * @var ProductListingPageRequest|MockObject
     */
    private $mockProductListingPageRequest;

    /**
     * @var ProductSearchService|MockObject
     */
    private $mockProductSearchService;

    /**
     * @var ProductListingRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest|MockObject
     */
    private $stubRequest;

    /**
     * @var UrlToWebsiteMap|MockObject
     */
    private $stubUrlToWebsiteMap;

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

        $this->stubRequest = $this->createMock(HttpRequest::class);

        $this->stubUrlToWebsiteMap = $this->createMock(UrlToWebsiteMap::class);

        /** @var SortBy|MockObject $stubDefaultSortBy */
        $stubDefaultSortBy = $this->createMock(SortBy::class);
        $this->mockProductSearchService = $this->createMock(ProductSearchService::class);

        $pageMeta = [
            ProductListingMetaSnippetContent::KEY_HANDLER_CODE => ProductListingRequestHandler::CODE,
            ProductListingMetaSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria' => [(new SearchCriterionAnything())->toArray()]
            ],
            ProductListingMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => 'root-snippet-code',
            ProductListingMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
            ProductListingMetaSnippetContent::KEY_PAGE_SPECIFIC_DATA => [],
        ];

        $this->requestHandler = new ProductListingRequestHandler(
            $stubContext,
            $stubFacetFilterRequest,
            $this->stubUrlToWebsiteMap,
            $stubProductListingPageContentBuilder,
            $this->mockProductListingPageRequest,
            $this->mockProductSearchService,
            $pageMeta,
            $stubDefaultSortBy
        );
    }

    public function testCanProcessAnyRequest(): void
    {
        $this->assertTrue($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testCookieProcessingIsTriggered(): void
    {
        $stubSortBy = $this->createMock(SortBy::class);

        $this->mockProductListingPageRequest->method('createSortByForRequest')->willReturn($stubSortBy);
        $this->mockProductListingPageRequest->method('getCurrentPageNumber')->willReturn(0);
        $this->mockProductListingPageRequest->expects($this->once())->method('processCookies');

        $this->requestHandler->process($this->stubRequest);
    }

    public function testHttpResponseIsReturned(): void
    {
        $stubSortBy = $this->createMock(SortBy::class);

        $this->mockProductListingPageRequest->method('createSortByForRequest')->willReturn($stubSortBy);
        $this->mockProductListingPageRequest->method('getCurrentPageNumber')->willReturn(0);

        $result = $this->requestHandler->process($this->stubRequest);

        $this->assertInstanceOf(HttpResponse::class, $result);
    }

    public function testSubsequentRequestToDataPoolIsMadeIfRequestedPageNumberIsGreaterThanTotalNumberOfPages(): void
    {
        $stubSortBy = $this->createMock(SortBy::class);

        $this->mockProductListingPageRequest->method('createSortByForRequest')->willReturn($stubSortBy);
        $this->mockProductListingPageRequest->method('getCurrentPageNumber')->willReturn(2);

        $this->mockProductSearchService->expects($this->exactly(2))->method('query');

        $this->requestHandler->process($this->stubRequest);
    }

    public function testNoSubsequentRequestToDataPoolIsMadeIfNoProductsAreFound(): void
    {
        $stubSortBy = $this->createMock(SortBy::class);
        $this->mockProductListingPageRequest->method('createSortByForRequest')->willReturn($stubSortBy);
        $this->mockProductListingPageRequest->method('getCurrentPageNumber')->willReturn(0);

        $this->mockProductSearchService->expects($this->once())->method('query');

        $this->requestHandler->process($this->stubRequest);
    }

    public function testSortByAttributeCodesAreMappedBeforePassedToSearchEngine(): void
    {
        $this->mockProductListingPageRequest->method('getCurrentPageNumber')->willReturn(0);
        $this->mockProductListingPageRequest->expects($this->once())->method('createSortByForRequest')
            ->willReturn($this->createMock(SortBy::class));

        $this->requestHandler->process($this->stubRequest);
    }
}
