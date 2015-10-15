<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\DefaultHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\UnableToHandleRequestException;
use LizardsAndPumpkins\PageBuilder;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductSearchRequestHandler
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingRequestHandlerTrait
 * @uses   \LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetContent
 */
class ProductSearchRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPageBuilder;

    /**
     * @var int
     */
    private $testDefaultNumberOfProductsPerPage = 1;

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
        $this->stubHttpRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME, $queryString],
        ]);
    }

    /**
     * @return DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubDataPoolReader()
    {
        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('getDocuments')->willReturn([$stubSearchDocument]);
        $stubSearchEngineResponse = $this->getMock(SearchEngineResponse::class, [], [], '', false);
        $stubSearchEngineResponse->method('getSearchDocuments')->willReturn($stubSearchDocumentCollection);

        $stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $stubDataPoolReader->method('getSearchResultsMatchingCriteria')->willReturn($stubSearchEngineResponse);
        $metaSnippetContent = [
            'root_snippet_code'  => 'foo',
            'page_snippet_codes' => ['foo']
        ];
        $stubDataPoolReader->method('getSnippet')->willReturn(json_encode($metaSnippetContent));
        $stubDataPoolReader->method('getSnippets')->willReturn([]);

        return $stubDataPoolReader;
    }

    protected function setUp()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $stubDataPoolReader = $this->createStubDataPoolReader();
        $this->mockPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);

        $stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        /** @var SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject $stubSnippetKeyGeneratorLocator */
        $stubSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $stubSnippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')->willReturn($stubSnippetKeyGenerator);

        $stubFilterNavigationAttributeCodes = ['foo'];
        $testSearchableAttributeCodes = ['foo'];

        $stubCriteria = $this->getMock(SearchCriteria::class);

        /** @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject $stubSearchCriteriaBuilder */
        $stubSearchCriteriaBuilder = $this->getMock(SearchCriteriaBuilder::class);
        $stubSearchCriteriaBuilder->method('anyOfFieldsContainString')->willReturn($stubCriteria);

        $this->requestHandler = new ProductSearchRequestHandler(
            $stubContext,
            $stubDataPoolReader,
            $this->mockPageBuilder,
            $stubSnippetKeyGeneratorLocator,
            $stubFilterNavigationAttributeCodes,
            $this->testDefaultNumberOfProductsPerPage,
            $stubSearchCriteriaBuilder,
            $testSearchableAttributeCodes
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

        $this->assertInstanceOf(DefaultHttpResponse::class, $this->requestHandler->process($this->stubHttpRequest));
    }
}
