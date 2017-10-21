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
     * @var ProductListingPageRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductListingPageRequest;

    /**
     * @var ProductSearchService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductSearchService;

    /**
     * @var ProductListingRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @var UrlToWebsiteMap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubUrlToWebsiteMap;

    private function prepareMockDataPoolReader(int $numberOfResults)
    {
        /** @var CompositeSearchCriterion|\PHPUnit_Framework_MockObject_MockObject $stubSelectionCriteria */
        $stubSelectionCriteria = $this->createMock(CompositeSearchCriterion::class);
        $stubSelectionCriteria->method('jsonSerialize')
            ->willReturn(['condition' => CompositeSearchCriterion::AND_CONDITION, 'criteria' => []]);
        $pageSnippetCodes = ['child-snippet1'];

        $testMetaInfoSnippetJson = json_encode(ProductListingMetaSnippetContent::create(
            $stubSelectionCriteria,
            'root-snippet-code',
            $pageSnippetCodes,
            $containers = [],
            $pageSpecificData = []
        )->toArray());

        $stubSearchEngineResponse = $this->createMock(SearchEngineResponse::class);
        $stubSearchEngineResponse->method('getTotalNumberOfResults')->willReturn($numberOfResults);

        $this->mockDataPoolReader->method('getSearchResults')->willReturn($stubSearchEngineResponse);
        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->testMetaInfoKey, $testMetaInfoSnippetJson],
        ]);
    }

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

        $this->stubRequest = $this->createMock(HttpRequest::class);

        $this->stubUrlToWebsiteMap = $this->createMock(UrlToWebsiteMap::class);

        /** @var SortBy|\PHPUnit_Framework_MockObject_MockObject $stubDefaultSortBy */
        $stubDefaultSortBy = $this->createMock(SortBy::class);
        $this->mockProductSearchService = $this->createMock(ProductSearchService::class);

        $pageMeta = [
            ProductListingSnippetContent::KEY_HANDLER_CODE => ProductListingRequestHandler::CODE,
            ProductListingSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria' => [(new SearchCriterionAnything())->toArray()]
            ],
            ProductListingSnippetContent::KEY_ROOT_SNIPPET_CODE => 'root-snippet-code',
            ProductListingSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingSnippetContent::KEY_CONTAINER_SNIPPETS => [],
            ProductListingSnippetContent::KEY_PAGE_SPECIFIC_DATA => [],
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

    public function testCanProcessAnyRequest()
    {
        $this->assertTrue($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testCookieProcessingIsTriggered()
    {
        $stubSortBy = $this->createMock(SortBy::class);

        $this->mockProductListingPageRequest->method('createSortByForRequest')->willReturn($stubSortBy);
        $this->mockProductListingPageRequest->method('getCurrentPageNumber')->willReturn(0);
        $this->mockProductListingPageRequest->expects($this->once())->method('processCookies');

        $this->requestHandler->process($this->stubRequest);
    }

    public function testHttpResponseIsReturned()
    {
        $stubSortBy = $this->createMock(SortBy::class);

        $this->mockProductListingPageRequest->method('createSortByForRequest')->willReturn($stubSortBy);
        $this->mockProductListingPageRequest->method('getCurrentPageNumber')->willReturn(0);

        $result = $this->requestHandler->process($this->stubRequest);

        $this->assertInstanceOf(HttpResponse::class, $result);
    }

    public function testSubsequentRequestToDataPoolIsMadeIfRequestedPageNumberIsGreaterThanTotalNumberOfPages()
    {
        $stubSortBy = $this->createMock(SortBy::class);

        $this->mockProductListingPageRequest->method('createSortByForRequest')->willReturn($stubSortBy);
        $this->mockProductListingPageRequest->method('getCurrentPageNumber')->willReturn(2);

        $this->mockProductSearchService->expects($this->exactly(2))->method('query');

        $this->requestHandler->process($this->stubRequest);
    }

    public function testNoSubsequentRequestToDataPoolIsMadeIfNoProductsAreFound()
    {
        $stubSortBy = $this->createMock(SortBy::class);
        $this->mockProductListingPageRequest->method('createSortByForRequest')->willReturn($stubSortBy);
        $this->mockProductListingPageRequest->method('getCurrentPageNumber')->willReturn(0);

        $this->mockProductSearchService->expects($this->once())->method('query');

        $this->requestHandler->process($this->stubRequest);
    }

    public function testSortByAttributeCodesAreMappedBeforePassedToSearchEngine()
    {
        $this->mockProductListingPageRequest->method('getCurrentPageNumber')->willReturn(0);
        $this->mockProductListingPageRequest->expects($this->once())->method('createSortByForRequest')
            ->willReturn($this->createMock(SortBy::class));

        $this->requestHandler->process($this->stubRequest);
    }
}
