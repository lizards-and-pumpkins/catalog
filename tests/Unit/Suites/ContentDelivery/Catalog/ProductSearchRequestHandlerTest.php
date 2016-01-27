<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Exception\UnableToHandleRequestException;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetContent;
use LizardsAndPumpkins\SnippetKeyGenerator;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductSearchRequestHandler
 * @uses   \LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetContent
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\QueryOptions
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 */
class ProductSearchRequestHandlerTest extends \PHPUnit_Framework_TestCase
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
     * @var string
     */
    private $testMetaInfoKey = 'stub-meta-info-key';

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @return DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubDataPoolReader()
    {
        /** @var CompositeSearchCriterion|\PHPUnit_Framework_MockObject_MockObject $stubSelectionCriteria */
        $stubSelectionCriteria = $this->getMock(CompositeSearchCriterion::class, [], [], '', false);
        $stubSelectionCriteria->method('jsonSerialize')
            ->willReturn(['condition' => CompositeSearchCriterion::AND_CONDITION, 'criteria' => []]);
        $pageSnippetCodes = ['child-snippet1'];

        $testMetaInfoSnippetJson = json_encode(ProductSearchResultMetaSnippetContent::create(
            'root-snippet-code',
            $pageSnippetCodes,
            []
        )->getInfo());

        $stubSearchEngineResponse = $this->getMock(SearchEngineResponse::class, [], [], '', false);

        $mockDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $mockDataPoolReader->method('getSearchResultsMatchingCriteria')->willReturn($stubSearchEngineResponse);
        $mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->testMetaInfoKey, $testMetaInfoSnippetJson]
        ]);

        return $mockDataPoolReader;
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
        $stubProductListingPageRequest->method('getCurrentPageNumber')->willReturn(0);

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

    /**
     * @return SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchCriteriaBuilder()
    {
        $stubSearchCriteria = $this->getMock(SearchCriteria::class);
        $stubSearchCriteriaBuilder = $this->getMock(SearchCriteriaBuilder::class, [], [], '', false);
        $stubSearchCriteriaBuilder->method('createCriteriaForAnyOfGivenFieldsContainsString')
            ->willReturn($stubSearchCriteria);

        return $stubSearchCriteriaBuilder;
    }

    protected function setUp()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);

        $stubDataPoolReader = $this->createStubDataPoolReader();

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->testMetaInfoKey);

        /** @var FacetFiltersToIncludeInResult|\PHPUnit_Framework_MockObject_MockObject  $stubFacetFilterRequest */
        $stubFacetFilterRequest = $this->getMock(FacetFiltersToIncludeInResult::class, [], [], '', false);

        $stubProductListingPageContentBuilder = $this->createStubProductListingPageContentBuilder();
        $stubSearchCriteriaBuilder = $this->createStubSearchCriteriaBuilder();
        $testSearchableAttributeCodes = [];

        $this->mockProductListingPageRequest = $this->createStubProductListingPageRequest();

        $this->stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);

        $this->requestHandler = new ProductSearchRequestHandler(
            $stubContext,
            $stubDataPoolReader,
            $stubSnippetKeyGenerator,
            $stubFacetFilterRequest,
            $stubSearchCriteriaBuilder,
            $testSearchableAttributeCodes,
            $stubProductListingPageContentBuilder,
            $this->mockProductListingPageRequest
        );
    }

    /**
     * @return HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    public function testRequestCanNotBeProcessedIfRequestUrlIsNotEqualToSearchPageUrl()
    {
        $this->stubRequest->method('getUrlPathRelativeToWebFront')->willReturn('foo');
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);

        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));

        return $this->stubRequest;
    }

    public function testRequestCanNotBeProcessedIfRequestMethodIsNotGet()
    {
        $this->stubRequest->method('getUrlPathRelativeToWebFront')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_POST);

        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testRequestCanNotBeProcessedIfQueryStringParameterIsNotPresent()
    {
        $this->stubRequest->method('getUrlPathRelativeToWebFront')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);

        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testRequestCanNotBeProcessedIfQueryStringIsEmpty()
    {
        $queryString = '';

        $this->stubRequest->method('getUrlPathRelativeToWebFront')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME, $queryString],
        ]);

        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));
    }

    /**
     * @depends testRequestCanNotBeProcessedIfRequestUrlIsNotEqualToSearchPageUrl
     * @param HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest
     */
    public function testExceptionIsThrownDuringAttemptToProcessInvalidRequest(HttpRequest $stubHttpRequest)
    {
        $this->setExpectedException(UnableToHandleRequestException::class);
        $this->requestHandler->process($stubHttpRequest);
    }

    /**
     * @return HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    public function testTrueIsReturnedIfRequestCanBeProcessed()
    {
        $this->stubRequest->method('getUrlPathRelativeToWebFront')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME, 'foo'],
        ]);

        $this->assertTrue($this->requestHandler->canProcess($this->stubRequest));

        return $this->stubRequest;
    }

    /**
     * @depends testTrueIsReturnedIfRequestCanBeProcessed
     * @param HttpRequest $stubRequest
     */
    public function testCookieProcessingIsTriggered(HttpRequest $stubRequest)
    {
        $this->mockProductListingPageRequest->expects($this->once())->method('processCookies');
        $this->requestHandler->process($stubRequest);
    }

    /**
     * @depends testTrueIsReturnedIfRequestCanBeProcessed
     * @param HttpRequest $stubRequest
     */
    public function testHttpResponseIsReturned(HttpRequest $stubRequest)
    {
        $result = $this->requestHandler->process($stubRequest);
        $this->assertInstanceOf(HttpResponse::class, $result);
    }
}
