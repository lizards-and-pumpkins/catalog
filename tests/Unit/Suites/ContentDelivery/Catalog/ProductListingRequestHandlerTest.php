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
use LizardsAndPumpkins\Product\ProductListingCriteriaSnippetContent;
use LizardsAndPumpkins\SnippetKeyGenerator;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingRequestHandler
 * @uses   \LizardsAndPumpkins\Product\ProductListingCriteriaSnippetContent
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
     * @return DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockDataPoolReader()
    {
        /** @var CompositeSearchCriterion|\PHPUnit_Framework_MockObject_MockObject $stubSelectionCriteria */
        $stubSelectionCriteria = $this->getMock(CompositeSearchCriterion::class, [], [], '', false);
        $stubSelectionCriteria->method('jsonSerialize')
            ->willReturn(['condition' => CompositeSearchCriterion::AND_CONDITION, 'criteria' => []]);
        $pageSnippetCodes = ['child-snippet1'];

        $testMetaInfoSnippetJson = json_encode(ProductListingCriteriaSnippetContent::create(
            $stubSelectionCriteria,
            'root-snippet-code',
            $pageSnippetCodes
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

        $this->mockDataPoolReader = $this->createMockDataPoolReader();

        /** @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGenerator */
        $stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);
        $stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->testMetaInfoKey);

        /** @var FacetFiltersToIncludeInResult|\PHPUnit_Framework_MockObject_MockObject $stubFacetFilterRequest */
        $stubFacetFilterRequest = $this->getMock(FacetFiltersToIncludeInResult::class, [], [], '', false);

        $stubProductListingPageContentBuilder = $this->createStubProductListingPageContentBuilder();

        $this->mockProductListingPageRequest = $this->createStubProductListingPageRequest();

        $this->stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);

        $this->requestHandler = new ProductListingRequestHandler(
            $stubContext,
            $this->mockDataPoolReader,
            $stubSnippetKeyGenerator,
            $stubFacetFilterRequest,
            $stubProductListingPageContentBuilder,
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
        $this->assertTrue($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testPageMetaInfoIsOnlyLoadedOnce()
    {
        $this->mockDataPoolReader->expects($this->once())->method('getSnippet')->with($this->testMetaInfoKey);
        $this->requestHandler->canProcess($this->stubRequest);
        $this->requestHandler->process($this->stubRequest);
    }

    public function testExceptionIsThrownIfProcessWithoutMetaInfoContentIsCalled()
    {
        $this->mockDataPoolReader->method('getSnippet')->willThrowException(new KeyNotFoundException);
        $this->setExpectedException(UnableToHandleRequestException::class);
        $this->requestHandler->process($this->stubRequest);
    }

    public function testCookieProcessingIsTriggered()
    {
        $this->mockDataPoolReader->expects($this->once())->method('getSnippet')->with($this->testMetaInfoKey);
        $this->mockProductListingPageRequest->expects($this->once())->method('processCookies');
        $this->requestHandler->process($this->stubRequest);
    }

    public function testHttpResponseIsReturned()
    {
        $this->mockDataPoolReader->expects($this->once())->method('getSnippet')->with($this->testMetaInfoKey);
        $result = $this->requestHandler->process($this->stubRequest);
        $this->assertInstanceOf(HttpResponse::class, $result);
    }
}
