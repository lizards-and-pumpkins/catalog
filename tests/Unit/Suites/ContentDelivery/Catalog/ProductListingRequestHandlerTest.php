<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyValue\Exception\KeyNotFoundException;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\Exception\UnableToHandleRequestException;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Product\ProductListingSnippetContent;
use LizardsAndPumpkins\SnippetKeyGenerator;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingRequestHandler
 * @uses   \LizardsAndPumpkins\Product\ProductListingSnippetContent
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\QueryOptions
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
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
        $stubSelectionCriteria = $this->getMock(CompositeSearchCriterion::class, [], [], '', false);
        $stubSelectionCriteria->method('jsonSerialize')
            ->willReturn(['condition' => CompositeSearchCriterion::AND_CONDITION, 'criteria' => []]);
        $pageSnippetCodes = ['child-snippet1'];

        $testMetaInfoSnippetJson = json_encode(ProductListingSnippetContent::create(
            $stubSelectionCriteria,
            'root-snippet-code',
            $pageSnippetCodes,
            []
        )->getInfo());

        $stubSearchEngineResponse = $this->getMock(SearchEngineResponse::class, [], [], '', false);
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
        $stubProductsPerPage = $this->getMock(ProductsPerPage::class, [], [], '', false);
        $stubProductsPerPage->method('getSelectedNumberOfProductsPerPage')->willReturn(1);

        $stubSortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);

        $stubProductListingPageRequest = $this->getMock(ProductListingPageRequest::class, [], [], '', false);
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
        $stubHttpResponse = $this->getMock(HttpResponse::class);
        $stubPageContentBuilder = $this->getMock(ProductListingPageContentBuilder::class, [], [], '', false);
        $stubPageContentBuilder->method('buildPageContent')->willReturn($stubHttpResponse);

        return $stubPageContentBuilder;
    }

    protected function setUp()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);

        $this->mockDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->testMetaInfoKey);

        /** @var FacetFiltersToIncludeInResult|\PHPUnit_Framework_MockObject_MockObject $stubFacetFilterRequest */
        $stubFacetFilterRequest = $this->getMock(FacetFiltersToIncludeInResult::class, [], [], '', false);

        $stubProductListingPageContentBuilder = $this->createStubProductListingPageContentBuilder();
        
        $stubSelectRobotsMetaTagContent = $this->getMock(SelectProductListingRobotsMetaTagContent::class);

        $this->mockProductListingPageRequest = $this->createStubProductListingPageRequest();

        $this->stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);

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

        $stubSortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);
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

        $stubSortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);
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

        $stubSortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);
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

        $stubSortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);
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

        $stubSortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);
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
            ->willReturn($this->getMock(SortOrderConfig::class, [], [], '', false));
        $this->requestHandler->process($this->stubRequest);
    }
}
