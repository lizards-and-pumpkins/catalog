<?php

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\UnableToHandleRequestException;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchAutosuggestionRequestHandler;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchAutosuggestionRequestHandler
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\ProductSearch\QueryOptions
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionMetaSnippetContent
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

        /** @var SortOrderConfig|\PHPUnit_Framework_MockObject_MockObject $sortOrderConfig */
        $sortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);

        $this->requestHandler = new ProductSearchAutosuggestionRequestHandler(
            $stubContext,
            $this->stubDataPoolReader,
            $this->mockPageBuilder,
            $stubKeyGeneratorLocator,
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

    public function testRequestCanNotBeProcessedIfQueryStringIsEmpty()
    {
        $queryString = '';
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
        $this->expectException(UnableToHandleRequestException::class);
        $this->requestHandler->process($this->stubHttpRequest);
    }

    public function testHttpResponseIsReturned()
    {
        $queryString = 'foo';
        $this->prepareStubHttpRequest($queryString);

        $this->mockPageBuilder->method('buildPage')->willReturn($this->getMock(HttpResponse::class, [], [], '', false));

        $metaSnippetContent = [
            PageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE  => 'foo',
            PageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => ['foo'],
            PageMetaInfoSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ];
        $this->stubDataPoolReader->method('getSnippet')->willReturn(json_encode($metaSnippetContent));
        $this->stubDataPoolReader->method('getSnippets')->willReturn([]);

        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);

        $stubSearchEngineResponse = $this->getMock(SearchEngineResponse::class, [], [], '', false);
        $stubSearchEngineResponse->method('getProductIds')->willReturn([$stubProductId]);

        $this->stubDataPoolReader->method('getSearchResultsMatchingString')->willReturn($stubSearchEngineResponse);

        $this->assertInstanceOf(HttpResponse::class, $this->requestHandler->process($this->stubHttpRequest));
    }

    public function testNoSnippetsAreAddedToPageBuilderIfNoSearchResultsAreReturned()
    {
        $queryString = 'foo';
        $this->prepareStubHttpRequest($queryString);

        $stubSearchEngineResponse = $this->getMock(SearchEngineResponse::class, [], [], '', false);
        $stubSearchEngineResponse->method('getProductIds')->willReturn([]);
        $this->stubDataPoolReader->method('getSearchResultsMatchingString')->willReturn($stubSearchEngineResponse);

        $metaSnippetContent = [
            PageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE  => 'foo',
            PageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => ['foo'],
            PageMetaInfoSnippetContent::KEY_CONTAINER_SNIPPETS => []
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

        $this->stubDataPoolReader->method('getSearchResultsMatchingString')->willReturn($stubSearchEngineResponse);

        $metaSnippetContent = [
            PageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE  => 'foo',
            PageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => ['foo'],
            PageMetaInfoSnippetContent::KEY_CONTAINER_SNIPPETS => []
        ];
        $this->stubDataPoolReader->method('getSnippet')->willReturn(json_encode($metaSnippetContent));
        $this->stubDataPoolReader->method('getSnippets')->willReturn([]);

        $this->mockPageBuilder->expects($this->atLeastOnce())->method('addSnippetsToPage');

        $this->requestHandler->process($this->stubHttpRequest);
    }
}
