<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpResponse;
use Brera\Http\HttpUrl;
use Brera\Http\UnableToHandleRequestException;
use Brera\PageBuilder;
use Brera\SnippetKeyGenerator;
use Brera\SnippetKeyGeneratorLocator;

/**
 * @covers \Brera\Product\ProductSearchAutosuggestionRequestHandler
 * @uses   \Brera\Product\ProductSearchAutosuggestionMetaSnippetContent
 */
class ProductSearchAutosuggestionRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubHttpUrl;

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
        $this->stubHttpUrl->method('getQueryParameter')
            ->with(ProductSearchAutosuggestionRequestHandler::QUERY_STRING_PARAMETER_NAME)
            ->willReturn($queryString);
        $this->stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
    }

    protected function setUp()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);

        $this->stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $this->mockPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);

        $mockSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        /** @var SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject $mockSnippetKeyGeneratorLocator */
        $mockSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $mockSnippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')->willReturn($mockSnippetKeyGenerator);

        $this->requestHandler = new ProductSearchAutosuggestionRequestHandler(
            $stubContext,
            $this->stubDataPoolReader,
            $this->mockPageBuilder,
            $mockSnippetKeyGeneratorLocator
        );

        $this->stubHttpUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $this->stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $this->stubHttpRequest->method('getUrl')->willReturn($this->stubHttpUrl);
    }

    public function testHttpRequestHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    public function testRequestCanNotBeProcessedIfRequestUrlIsNotEqualToSearchAutosuggestionUrl()
    {
        $urlString = 'foo';
        $this->stubHttpUrl->method('getPathRelativeToWebFront')->willReturn($urlString);
        $this->stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubHttpRequest->method('getQueryParameter')
            ->with(ProductSearchAutosuggestionRequestHandler::QUERY_STRING_PARAMETER_NAME)
            ->willReturn('bar');

        $this->assertFalse($this->requestHandler->canProcess($this->stubHttpRequest));
    }

    public function testRequestCanNotBeProcessedIfRequestMethodIsNotGet()
    {
        $urlString = ProductSearchAutosuggestionRequestHandler::SEARCH_RESULTS_SLUG;
        $this->stubHttpUrl->method('getPathRelativeToWebFront')->willReturn($urlString);
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
        $this->stubDataPoolReader->method('getSearchResults')->willReturn([]);

        $this->assertInstanceOf(HttpResponse::class, $this->requestHandler->process($this->stubHttpRequest));
    }

    public function testSearchResultsAreAddedToPageBuilder()
    {
        $queryString = 'foo';
        $this->prepareStubHttpRequest($queryString);

        $this->stubDataPoolReader->method('getSearchResults')->willReturn(['product_in_listing_id']);

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
