<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Exception\UnableToHandleRequestException;
use LizardsAndPumpkins\ContentDelivery\PageBuilder;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\SnippetKeyGeneratorLocator;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductSearchAutosuggestionRequestHandler
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\Product\ProductSearchAutosuggestionMetaSnippetContent
 */
class ProductSearchAutosuggestionRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPageBuilder;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDataPoolReader;

    /**
     * @var ProductSearchAutosuggestionRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubHttpRequest;

    /**
     * @param string $queryString
     */
    private function prepareStubHttpRequest($queryString)
    {
        $urlString = ProductSearchAutosuggestionRequestHandler::SEARCH_RESULTS_SLUG;
        $this->stubHttpRequest->method('getUrlPathRelativeToWebFront')->willReturn($urlString);
        $this->stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubHttpRequest->method('getQueryParameter')
            ->with(ProductSearchAutosuggestionRequestHandler::QUERY_STRING_PARAMETER_NAME)
            ->willReturn($queryString);
    }

    protected function setUp()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);

        $this->stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $this->mockPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);

        $stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        /** @var SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject $stubKeyGeneratorLocator */
        $stubKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $stubKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')->willReturn($stubSnippetKeyGenerator);

        $stubCriteria = $this->getMock(SearchCriteria::class);

        /** @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject $stubSearchCriteriaBuilder */
        $stubSearchCriteriaBuilder = $this->getMock(SearchCriteriaBuilder::class, [], [], '', false);
        $stubSearchCriteriaBuilder->method('createCriteriaForAnyOfGivenFieldsContainsString')
            ->willReturn($stubCriteria);

        $testSearchableAttributeCodes = ['foo'];

        /** @var SortOrderConfig|\PHPUnit_Framework_MockObject_MockObject $sortOrderConfig */
        $sortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);

        $this->requestHandler = new ProductSearchAutosuggestionRequestHandler(
            $stubContext,
            $this->stubDataPoolReader,
            $this->mockPageBuilder,
            $stubKeyGeneratorLocator,
            $stubSearchCriteriaBuilder,
            $testSearchableAttributeCodes,
            $sortOrderConfig
        );

        $this->stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
    }

    public function testHttpRequestHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    public function testRequestCanNotBeProcessedIfRequestUrlIsNotEqualToSearchAutosuggestionUrl()
    {
        $urlString = 'foo';
        $this->stubHttpRequest->method('getUrlPathRelativeToWebFront')->willReturn($urlString);
        $this->stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubHttpRequest->method('getQueryParameter')
            ->with(ProductSearchAutosuggestionRequestHandler::QUERY_STRING_PARAMETER_NAME)
            ->willReturn('bar');

        $this->assertFalse($this->requestHandler->canProcess($this->stubHttpRequest));
    }

    public function testRequestCanNotBeProcessedIfRequestMethodIsNotGet()
    {
        $urlString = ProductSearchAutosuggestionRequestHandler::SEARCH_RESULTS_SLUG;
        $this->stubHttpRequest->method('getUrlPathRelativeToWebFront')->willReturn($urlString);
        $this->stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_POST);
        $this->stubHttpRequest->method('getQueryParameter')
            ->with(ProductSearchAutosuggestionRequestHandler::QUERY_STRING_PARAMETER_NAME)
            ->willReturn('foo');

        $this->assertFalse($this->requestHandler->canProcess($this->stubHttpRequest));
    }

    public function testRequestCanNotBeProcessedIfQueryStringParameterIsNotPresent()
    {
        $this->prepareStubHttpRequest(null);
        $this->assertFalse($this->requestHandler->canProcess($this->stubHttpRequest));
    }

    public function testRequestCanNotBeProcessedIfQueryStringIsShorterThenMinimalAllowedLength()
    {
        $queryString = 'f';
        $this->prepareStubHttpRequest($queryString);
        $this->stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);

        $this->assertFalse($this->requestHandler->canProcess($this->stubHttpRequest));
    }

    public function testRequestCanBeHandledIfValidSearchRequest()
    {
        $queryString = 'foo';
        $this->prepareStubHttpRequest($queryString);
        $this->assertTrue($this->requestHandler->canProcess($this->stubHttpRequest));
    }

    /**
     * @depends testRequestCanNotBeProcessedIfRequestUrlIsNotEqualToSearchAutosuggestionUrl
     */
    public function testExceptionIsThrownDuringAttemptToProcessInvalidRequest()
    {
        $this->setExpectedException(UnableToHandleRequestException::class);
        $this->requestHandler->process($this->stubHttpRequest);
    }

    public function testHttpResponseIsReturned()
    {
        $queryString = 'foo';
        $this->prepareStubHttpRequest($queryString);

        $this->mockPageBuilder->method('buildPage')->willReturn($this->getMock(HttpResponse::class, [], [], '', false));

        $metaSnippetContent = [
            'root_snippet_code'  => 'foo',
            'page_snippet_codes' => ['foo']
        ];
        $this->stubDataPoolReader->method('getSnippet')->willReturn(json_encode($metaSnippetContent));
        $this->stubDataPoolReader->method('getSnippets')->willReturn([]);

        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        $stubSearchEngineResponse = $this->getMock(SearchEngineResponse::class, [], [], '', false);
        $stubSearchEngineResponse->method('getProductIds')->willReturn([$stubProductId]);

        $this->stubDataPoolReader->method('getSearchResultsMatchingCriteria')->willReturn($stubSearchEngineResponse);

        $this->assertInstanceOf(HttpResponse::class, $this->requestHandler->process($this->stubHttpRequest));
    }

    public function testNoSnippetsAreAddedToPageBuilderIfNoSearchResultsAreReturned()
    {
        $queryString = 'foo';
        $this->prepareStubHttpRequest($queryString);

        $stubSearchEngineResponse = $this->getMock(SearchEngineResponse::class, [], [], '', false);
        $stubSearchEngineResponse->method('getProductIds')->willReturn([]);
        $this->stubDataPoolReader->method('getSearchResultsMatchingCriteria')->willReturn($stubSearchEngineResponse);

        $metaSnippetContent = [
            'root_snippet_code'  => 'foo',
            'page_snippet_codes' => ['foo']
        ];
        $this->stubDataPoolReader->method('getSnippet')->willReturn(json_encode($metaSnippetContent));
        $this->stubDataPoolReader->expects($this->never())->method('getSnippets');

        $this->requestHandler->process($this->stubHttpRequest);
    }

    public function testSearchResultsAreAddedToPageBuilder()
    {
        $queryString = 'foo';
        $this->prepareStubHttpRequest($queryString);

        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        $stubSearchEngineResponse = $this->getMock(SearchEngineResponse::class, [], [], '', false);
        $stubSearchEngineResponse->method('getProductIds')->willReturn([$stubProductId]);

        $this->stubDataPoolReader->method('getSearchResultsMatchingCriteria')->willReturn($stubSearchEngineResponse);

        $metaSnippetContent = [
            'root_snippet_code'  => 'foo',
            'page_snippet_codes' => ['foo']
        ];
        $this->stubDataPoolReader->method('getSnippet')->willReturn(json_encode($metaSnippetContent));
        $this->stubDataPoolReader->method('getSnippets')->willReturn([]);

        $this->mockPageBuilder->expects($this->atLeastOnce())->method('addSnippetsToPage');

        $this->requestHandler->process($this->stubHttpRequest);
    }
}
