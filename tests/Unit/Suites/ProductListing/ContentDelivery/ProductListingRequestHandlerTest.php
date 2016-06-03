<?php

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\KeyNotFoundException;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\UnableToHandleRequestException;
use LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetContent;

/**
 * @covers \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingRequestHandler
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetContent
 * @uses   \LizardsAndPumpkins\ProductSearch\QueryOptions
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class ProductListingRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingPageRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductListingPageRequest;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

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
     * @param int $numberOfResults
     * @return DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private function prepareMockDataPoolReader($numberOfResults)
    {
        /** @var CompositeSearchCriterion|\PHPUnit_Framework_MockObject_MockObject $stubSelectionCriteria */
        $stubSelectionCriteria = $this->createMock(CompositeSearchCriterion::class);
        $stubSelectionCriteria->method('jsonSerialize')
            ->willReturn(['condition' => CompositeSearchCriterion::AND_CONDITION, 'criteria' => []]);
        $pageSnippetCodes = ['child-snippet1'];

        $testMetaInfoSnippetJson = json_encode(ProductListingSnippetContent::create(
            $stubSelectionCriteria,
            'root-snippet-code',
            $pageSnippetCodes,
            []
        )->getInfo());

        $stubSearchEngineResponse = $this->createMock(SearchEngineResponse::class);
        $stubSearchEngineResponse->method('getTotalNumberOfResults')->willReturn($numberOfResults);

        $this->mockDataPoolReader->method('getSearchResultsMatchingCriteria')->willReturn($stubSearchEngineResponse);
        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->testMetaInfoKey, $testMetaInfoSnippetJson],
        ]);
    }

    /**
     * @return ProductListingPageRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProductListingPageRequest()
    {
        $stubProductsPerPage = $this->createMock(ProductsPerPage::class);
        $stubProductsPerPage->method('getSelectedNumberOfProductsPerPage')->willReturn(1);

        $stubSortOrderConfig = $this->createMock(SortOrderConfig::class);

        $stubProductListingPageRequest = $this->createMock(ProductListingPageRequest::class);
        $stubProductListingPageRequest->method('getProductsPerPage')->willReturn($stubProductsPerPage);
        $stubProductListingPageRequest->method('getSelectedSortOrderConfig')->willReturn($stubSortOrderConfig);
        $stubProductListingPageRequest->method('getSelectedFilterValues')->willReturn([]);

        return $stubProductListingPageRequest;
    }

    /**
     * @return ProductListingPageContentBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProductListingPageContentBuilder()
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

        $this->mockDataPoolReader = $this->createMock(DataPoolReader::class);

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->createMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->testMetaInfoKey);

        /** @var FacetFiltersToIncludeInResult|\PHPUnit_Framework_MockObject_MockObject $stubFacetFilterRequest */
        $stubFacetFilterRequest = $this->createMock(FacetFiltersToIncludeInResult::class);

        $stubProductListingPageContentBuilder = $this->createStubProductListingPageContentBuilder();

        $stubSelectRobotsMetaTagContent = $this->createMock(SelectProductListingRobotsMetaTagContent::class);

        $this->mockProductListingPageRequest = $this->createStubProductListingPageRequest();

        $this->stubRequest = $this->createMock(HttpRequest::class);

        $this->requestHandler = new ProductListingRequestHandler(
            $stubContext,
            $this->mockDataPoolReader,
            $stubSnippetKeyGenerator,
            $stubFacetFilterRequest,
            $stubProductListingPageContentBuilder,
            $stubSelectRobotsMetaTagContent,
            $this->mockProductListingPageRequest
        );
    }

    public function testFalseIsReturnedIfThePageMetaInfoContentSnippetCanNotBeLoaded()
    {
        $this->mockDataPoolReader->method('getSnippet')->willThrowException(new KeyNotFoundException);
        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testTrueIsReturnedIfThePageMetaInfoContentSnippetCanBeLoaded()
    {
        $numberOfResults = 1;
        $this->prepareMockDataPoolReader($numberOfResults);

        $this->assertTrue($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testPageMetaInfoIsOnlyLoadedOnce()
    {
        $numberOfResults = 1;
        $this->prepareMockDataPoolReader($numberOfResults);

        $stubSortOrderConfig = $this->createMock(SortOrderConfig::class);
        $this->mockProductListingPageRequest->method('createSortOrderConfigForRequest')
            ->willReturn($stubSortOrderConfig);

        $this->mockProductListingPageRequest->method('getCurrentPageNumber')->willReturn(0);
        $this->mockDataPoolReader->expects($this->once())->method('getSnippet')->with($this->testMetaInfoKey);
        $this->requestHandler->canProcess($this->stubRequest);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testExceptionIsThrownIfProcessWithoutMetaInfoContentIsCalled()
    {
        $this->mockDataPoolReader->method('getSnippet')->willThrowException(new KeyNotFoundException);
        $this->expectException(UnableToHandleRequestException::class);
        $this->requestHandler->process($this->stubRequest);
    }

    public function testCookieProcessingIsTriggered()
    {
        $numberOfResults = 1;
        $this->prepareMockDataPoolReader($numberOfResults);

        $stubSortOrderConfig = $this->createMock(SortOrderConfig::class);
        $this->mockProductListingPageRequest->method('createSortOrderConfigForRequest')
            ->willReturn($stubSortOrderConfig);

        $this->mockProductListingPageRequest->method('getCurrentPageNumber')->willReturn(0);
        $this->mockDataPoolReader->expects($this->once())->method('getSnippet')->with($this->testMetaInfoKey);
        $this->mockProductListingPageRequest->expects($this->once())->method('processCookies');

        $this->requestHandler->process($this->stubRequest);
    }

    public function testHttpResponseIsReturned()
    {
        $numberOfResults = 1;
        $this->prepareMockDataPoolReader($numberOfResults);

        $stubSortOrderConfig = $this->createMock(SortOrderConfig::class);
        $this->mockProductListingPageRequest->method('createSortOrderConfigForRequest')
            ->willReturn($stubSortOrderConfig);

        $this->mockProductListingPageRequest->method('getCurrentPageNumber')->willReturn(0);
        $this->mockDataPoolReader->expects($this->once())->method('getSnippet')->with($this->testMetaInfoKey);
        $result = $this->requestHandler->process($this->stubRequest);

        $this->assertInstanceOf(HttpResponse::class, $result);
    }

    public function testSubsequentRequestToDataPoolIsMadeIfRequestedPageNumberIsGreaterThanTotalNumberOfPages()
    {
        $numberOfResults = 1;
        $this->prepareMockDataPoolReader($numberOfResults);

        $stubSortOrderConfig = $this->createMock(SortOrderConfig::class);
        $this->mockProductListingPageRequest->method('createSortOrderConfigForRequest')
            ->willReturn($stubSortOrderConfig);

        $this->mockProductListingPageRequest->method('getCurrentPageNumber')->willReturn(2);
        $this->mockDataPoolReader->expects($this->exactly(2))->method('getSearchResultsMatchingCriteria');
        $this->requestHandler->process($this->stubRequest);
    }

    public function testNoSubsequentRequestToDataPoolIsMadeIfNoProductsAreFound()
    {
        $numberOfResults = 0;
        $this->prepareMockDataPoolReader($numberOfResults);

        $stubSortOrderConfig = $this->createMock(SortOrderConfig::class);
        $this->mockProductListingPageRequest->method('createSortOrderConfigForRequest')
            ->willReturn($stubSortOrderConfig);

        $this->mockProductListingPageRequest->method('getCurrentPageNumber')->willReturn(0);
        $this->mockDataPoolReader->expects($this->once())->method('getSearchResultsMatchingCriteria');
        $this->requestHandler->process($this->stubRequest);
    }

    public function testSortOrderConfigAttributeCodesAreMappedBeforePassedToSearchEngine()
    {
        $numberOfResults = 1;
        $this->prepareMockDataPoolReader($numberOfResults);

        $this->mockProductListingPageRequest->method('getCurrentPageNumber')->willReturn(0);

        $this->mockProductListingPageRequest->expects($this->once())->method('createSortOrderConfigForRequest')
            ->willReturn($this->createMock(SortOrderConfig::class));
        $this->requestHandler->process($this->stubRequest);
    }
}
