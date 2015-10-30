<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineFacetFieldCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\PageBuilder;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator;

abstract class AbstractProductListingRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

    /**
     * @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPageBuilder;

    /**
     * @var int
     */
    private $testDefaultNumberOfProductsPerPage = 1;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @var HttpRequestHandler
     */
    private $requestHandler;

    private function prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection()
    {
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection();
        $this->prepareMockDataPoolReaderWithStubSearchDocumentCollection($stubSearchDocumentCollection);
    }

    private function prepareMockDataPoolReaderWithStubSearchDocumentCollection(
        \PHPUnit_Framework_MockObject_MockObject $documentCollection
    ) {
        $stubFacetFieldsCollection = $this->getMock(SearchEngineFacetFieldCollection::class, [], [], '', false);

        $stubSearchEngineResponse = $this->getMock(SearchEngineResponse::class, [], [], '', false);
        $stubSearchEngineResponse->method('getSearchDocuments')->willReturn($documentCollection);
        $stubSearchEngineResponse->method('getFacetFieldCollection')->willReturn($stubFacetFieldsCollection);

        $this->mockDataPoolReader->method('getSearchResultsMatchingCriteria')->willReturn($stubSearchEngineResponse);
    }

    /**
     * @return SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentCollection()
    {
        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('getDocuments')->willReturn([$stubSearchDocument]);
        $stubSearchDocumentCollection->method('count')->willReturn(1);

        return $stubSearchDocumentCollection;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     */
    private function createAddedSnippetsSpy()
    {
        $addSnippetsToPageSpy = $this->any();
        $this->mockPageBuilder->expects($addSnippetsToPageSpy)->method('addSnippetsToPage');
        return $addSnippetsToPageSpy;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedRecorder $spy
     * @param string $snippetCode
     */
    private function assertDynamicSnippetWithAnyValueWasAddedToPageBuilder(
        \PHPUnit_Framework_MockObject_Matcher_InvokedRecorder $spy,
        $snippetCode
    ) {
        $numberOfTimesSnippetWasAddedToPageBuilder = array_sum(array_map(function ($invocation) use ($snippetCode) {
            return intval([$snippetCode => $snippetCode] === $invocation->parameters[0]);
        }, $spy->getInvocations()));

        $this->assertEquals(
            1,
            $numberOfTimesSnippetWasAddedToPageBuilder,
            sprintf('Failed to assert "%s" snippet was added to page builder.', $snippetCode)
        );
    }

    /**
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedRecorder $spy
     * @param string $snippetCode
     * @param string $snippetValue
     */
    private function assertDynamicSnippetWasAddedToPageBuilder(
        \PHPUnit_Framework_MockObject_Matcher_InvokedRecorder $spy,
        $snippetCode,
        $snippetValue
    ) {
        $numberOfTimesSnippetWasAddedToPageBuilder = array_sum(
            array_map(function ($invocation) use ($snippetCode, $snippetValue) {
                return intval([$snippetCode => $snippetCode] === $invocation->parameters[0] &&
                              [$snippetCode => $snippetValue] === $invocation->parameters[1]);
            }, $spy->getInvocations())
        );

        $this->assertEquals(1, $numberOfTimesSnippetWasAddedToPageBuilder, sprintf(
            'Failed to assert "%s" snippet with "%s" value was added to page builder.',
            $snippetCode,
            $snippetValue
        ));
    }

    /**
     * @param Context $context
     * @param DataPoolReader $dataPoolReader
     * @param PageBuilder $pageBuilder
     * @param SnippetKeyGeneratorLocator $snippetKeyGeneratorLocator
     * @param array[] $filterNavigationConfig
     * @param int $defaultNumberOfProductsPerPage
     * @return HttpRequestHandler
     */
    abstract protected function createRequestHandler(
        Context $context,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder,
        SnippetKeyGeneratorLocator $snippetKeyGeneratorLocator,
        array $filterNavigationConfig,
        $defaultNumberOfProductsPerPage
    );

    /**
     * @return SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    abstract protected function createStubSnippetKeyGeneratorLocator();

    /**
     * @return DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    abstract protected function createStubDataPoolReader();

    /**
     * @return HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    abstract protected function createStubRequest();

    protected function setUp()
    {
        $this->mockDataPoolReader = $this->createStubDataPoolReader();
        $this->mockPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $stubSnippetKeyGeneratorLocator = $this->createStubSnippetKeyGeneratorLocator();

        $testFilterNavigationConfig = ['foo' => []];

        $this->requestHandler = $this->createRequestHandler(
            $stubContext,
            $this->mockDataPoolReader,
            $this->mockPageBuilder,
            $stubSnippetKeyGeneratorLocator,
            $testFilterNavigationConfig,
            $this->testDefaultNumberOfProductsPerPage
        );

        $this->stubRequest = $this->createStubRequest();
    }

    public function testHttpRequestHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    public function testNumberOfProductsPerPageSnippetWithDefaultNumberOfProductsPerPageIsAddedToPageBuilder()
    {
        $snippetCode = 'products_per_page';
        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $addSnippetsToPageSpy = $this->createAddedSnippetsSpy();

        $this->requestHandler->process($this->stubRequest);

        $this->assertDynamicSnippetWasAddedToPageBuilder(
            $addSnippetsToPageSpy,
            $snippetCode,
            $this->testDefaultNumberOfProductsPerPage
        );
    }

    public function testNumberOfProductsPerPageSnippetWithNumberOfProductsPerPageStoredInCookieIsAddedToPageBuilder()
    {
        $snippetCode = 'products_per_page';
        $productsPerPage = 2;

        $this->stubRequest->method('hasCookie')->with(ProductListingRequestHandler::PRODUCTS_PER_PAGE_COOKIE_NAME)
            ->willReturn(true);
        $this->stubRequest->method('getCookieValue')->with(ProductListingRequestHandler::PRODUCTS_PER_PAGE_COOKIE_NAME)
            ->willReturn($productsPerPage);

        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $addSnippetsToPageSpy = $this->createAddedSnippetsSpy();

        $this->requestHandler->process($this->stubRequest);

        $this->assertDynamicSnippetWasAddedToPageBuilder($addSnippetsToPageSpy, $snippetCode, $productsPerPage);
    }

    public function testPageIsReturned()
    {
        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $this->mockPageBuilder->method('buildPage')->willReturn($this->getMock(HttpResponse::class, [], [], '', false));

        $this->assertInstanceOf(HttpResponse::class, $this->requestHandler->process($this->stubRequest));
    }

    public function testNoSnippetsAreAddedToPageBuilderIfListingIsEmpty()
    {
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('count')->willReturn(0);
        $this->prepareMockDataPoolReaderWithStubSearchDocumentCollection($stubSearchDocumentCollection);

        $this->mockPageBuilder->expects($this->never())->method('addSnippetsToPage');

        $this->requestHandler->process($this->stubRequest);
    }

    public function testProductsInListingAreAddedToPageBuilder()
    {
        $productGridSnippetCode = 'product_grid';
        $productPricesSnippetCode = 'product_prices';

        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $addSnippetsToPageSpy = $this->createAddedSnippetsSpy();

        $this->requestHandler->process($this->stubRequest);

        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($addSnippetsToPageSpy, $productGridSnippetCode);
        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($addSnippetsToPageSpy, $productPricesSnippetCode);
    }

    public function testFilterNavigationSnippetIsAddedToPageBuilder()
    {
        $snippetCode = 'filter_navigation';
        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $addSnippetsToPageSpy = $this->createAddedSnippetsSpy();

        $this->requestHandler->process($this->stubRequest);

        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($addSnippetsToPageSpy, $snippetCode);
    }

    public function testTotalNumberOfResultsSnippetIsAddedToPageBuilder()
    {
        $snippetCode = 'total_number_of_results';
        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $addSnippetsToPageSpy = $this->createAddedSnippetsSpy();

        $this->requestHandler->process($this->stubRequest);

        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($addSnippetsToPageSpy, $snippetCode);
    }

    public function testDefaultNumberOfProductsPerPageIsRequestedFromDataPool()
    {
        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();

        $spy = $this->any();
        $this->mockDataPoolReader->expects($spy)->method('getSearchResultsMatchingCriteria');

        $this->requestHandler->process($this->stubRequest);

        array_map(function (\PHPUnit_Framework_MockObject_Invocation_Static $invocation) {
            $this->assertSame($this->testDefaultNumberOfProductsPerPage, $invocation->parameters[4]);
        }, $spy->getInvocations());
    }

    public function testNumberOfProductsPerPageStoredInCookieIsRequestedFromDataPool()
    {
        $productsPerPage = 2;

        $this->stubRequest->method('hasCookie')->with(ProductListingRequestHandler::PRODUCTS_PER_PAGE_COOKIE_NAME)
            ->willReturn(true);
        $this->stubRequest->method('getCookieValue')->with(ProductListingRequestHandler::PRODUCTS_PER_PAGE_COOKIE_NAME)
            ->willReturn($productsPerPage);

        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();

        $spy = $this->any();
        $this->mockDataPoolReader->expects($spy)->method('getSearchResultsMatchingCriteria');

        $this->requestHandler->process($this->stubRequest);

        array_map(function (\PHPUnit_Framework_MockObject_Invocation_Static $invocation) use ($productsPerPage) {
            $this->assertSame($productsPerPage, $invocation->parameters[4]);
        }, $spy->getInvocations());
    }
}
