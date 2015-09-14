<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DefaultHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\UnableToHandleRequestException;
use LizardsAndPumpkins\PageBuilder;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator;

/**
 * @covers \LizardsAndPumpkins\Product\ProductSearchRequestHandler
 * @uses   \LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetContent
 */
class ProductSearchRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPageBuilder;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

    /**
     * @var ProductSearchRequestHandler
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
        $urlString = ProductSearchRequestHandler::SEARCH_RESULTS_SLUG;
        $this->stubHttpRequest->method('getUrlPathRelativeToWebFront')->willReturn($urlString);
        $this->stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubHttpRequest->method('getQueryParameter')
            ->with(ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME)
            ->willReturn($queryString);
    }

    protected function setUp()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);

        $this->mockDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $this->mockPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);

        $mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        /** @var SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject $mockSnippetKeyGeneratorLocator */
        $mockSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $mockSnippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')->willReturn($mockSnippetKeyGenerator);

        $this->requestHandler = new ProductSearchRequestHandler(
            $stubContext,
            $this->mockDataPoolReader,
            $this->mockPageBuilder,
            $mockSnippetKeyGeneratorLocator
        );

        $this->stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
    }

    public function testHttpRequestHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    public function testRequestCanNotBeProcessedIfRequestUrlIsNotEqualToSearchPageUrl()
    {
        $urlString = 'foo';
        $this->stubHttpRequest->method('getUrlPathRelativeToWebFront')->willReturn($urlString);
        $this->stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);

        $this->assertFalse($this->requestHandler->canProcess($this->stubHttpRequest));
    }

    public function testRequestCanNotBeProcessedIfRequestMethodIsNotGet()
    {
        $urlString = ProductSearchRequestHandler::SEARCH_RESULTS_SLUG;
        $this->stubHttpRequest->method('getUrlPathRelativeToWebFront')->willReturn($urlString);
        $this->stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_POST);

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
     * @depends testRequestCanNotBeProcessedIfRequestUrlIsNotEqualToSearchPageUrl
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

        $this->mockPageBuilder->method('buildPage')
            ->willReturn($this->getMock(DefaultHttpResponse::class, [], [], '', false));

        $metaSnippetContent = [
            'root_snippet_code'  => 'foo',
            'page_snippet_codes' => ['foo']
        ];
        $this->mockDataPoolReader->method('getSnippet')->willReturn(json_encode($metaSnippetContent));

        $this->assertInstanceOf(DefaultHttpResponse::class, $this->requestHandler->process($this->stubHttpRequest));
    }

    public function testSearchResultsAreAddedToPageBuilder()
    {
        $queryString = 'foo';
        $this->prepareStubHttpRequest($queryString);

        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('getDocuments')->willReturn([$stubSearchDocument]);

        $this->mockDataPoolReader->method('getSearchResults')->willReturn($stubSearchDocumentCollection);

        $metaSnippetContent = [
            'root_snippet_code'  => 'foo',
            'page_snippet_codes' => ['foo']
        ];
        $this->mockDataPoolReader->method('getSnippet')->willReturn(json_encode($metaSnippetContent));
        $this->mockDataPoolReader->method('getSnippets')->willReturn([]);

        $this->mockPageBuilder->expects($this->once())->method('addSnippetsToPage');

        $this->requestHandler->process($this->stubHttpRequest);
    }
}
