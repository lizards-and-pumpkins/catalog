<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\Catalog\Exception\NoSelectedSortOrderException;
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
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\SnippetKeyGeneratorLocator;

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

    /**
     * @var SortOrderConfig[]|\PHPUnit_Framework_MockObject_MockObject[] $stubSortOrderConfigs
     */
    private $stubSortOrderConfigs;

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
     * @param int $productsPerPage
     */
    private function setExpectedNumberOfProductPerPage($productsPerPage)
    {
        $this->mockDataPoolReader->expects($this->once())->method('getSearchResultsMatchingCriteria')->with(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $productsPerPage
        );
    }

    /**
     * @param SortOrderConfig|\PHPUnit_Framework_MockObject_MockObject $expectedSortOrderConfig
     */
    private function setExpectedSortOrderConfig($expectedSortOrderConfig)
    {
        $this->mockDataPoolReader->expects($this->once())->method('getSearchResultsMatchingCriteria')->with(
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $this->anything(),
            $expectedSortOrderConfig
        );
    }

    /**
     * @param Context $context
     * @param DataPoolReader $dataPoolReader
     * @param PageBuilder $pageBuilder
     * @param SnippetKeyGeneratorLocator $snippetKeyGeneratorLocator
     * @param array[] $filterNavigationConfig
     * @param ProductsPerPage $productsPerPage
     * @param SortOrderConfig[] $sortOrderConfigs
     * @return HttpRequestHandler
     */
    abstract protected function createRequestHandler(
        Context $context,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder,
        SnippetKeyGeneratorLocator $snippetKeyGeneratorLocator,
        array $filterNavigationConfig,
        ProductsPerPage $productsPerPage,
        SortOrderConfig ...$sortOrderConfigs
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
        $productsPerPage = ProductsPerPage::create([1, 2, 3], $this->testDefaultNumberOfProductsPerPage);

        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);

        $stubSortOrderDirection = $this->getMock(SortOrderDirection::class, [], [], '', false);

        $stubUnselectedSortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);
        $stubUnselectedSortOrderConfig->method('isSelected')->willReturn(false);
        $stubUnselectedSortOrderConfig->method('getSelectedDirection')->willReturn($stubSortOrderDirection);
        $stubUnselectedSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCode);

        $stubSelectedSortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);
        $stubSelectedSortOrderConfig->method('isSelected')->willReturn(true);
        $stubSelectedSortOrderConfig->method('getSelectedDirection')->willReturn($stubSortOrderDirection);
        $stubSelectedSortOrderConfig->method('getAttributeCode')->willReturn($stubAttributeCode);

        $this->stubSortOrderConfigs = [$stubUnselectedSortOrderConfig, $stubSelectedSortOrderConfig];

        $this->requestHandler = $this->createRequestHandler(
            $stubContext,
            $this->mockDataPoolReader,
            $this->mockPageBuilder,
            $stubSnippetKeyGeneratorLocator,
            $testFilterNavigationConfig,
            $productsPerPage,
            ...$this->stubSortOrderConfigs
        );

        $this->stubRequest = $this->createStubRequest();
    }

    public function testHttpRequestHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    public function testNumberOfProductsPerPageSnippetWithFirstAvailableNumberOfProductsPerPageIsAddedToPageBuilder()
    {
        $snippetCode = 'products_per_page';
        $expectedSnippetContent = json_encode([
            ['number' => 1, 'selected' => true],
            ['number' => 2, 'selected' => false],
            ['number' => 3, 'selected' => false],
        ]);

        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $addSnippetsToPageSpy = $this->createAddedSnippetsSpy();

        $this->requestHandler->process($this->stubRequest);

        $this->assertDynamicSnippetWasAddedToPageBuilder($addSnippetsToPageSpy, $snippetCode, $expectedSnippetContent);
    }

    public function testNumberOfProductsPerPageSnippetWithNumberOfProductsPerPageStoredInCookieIsAddedToPageBuilder()
    {
        $productsPerPage = 2;

        $snippetCode = 'products_per_page';
        $expectedSnippetContent = json_encode([
            ['number' => 1, 'selected' => false],
            ['number' => 2, 'selected' => true],
            ['number' => 3, 'selected' => false],
        ]);

        $this->stubRequest->method('hasCookie')->willReturnMap([
            [ProductListingRequestHandler::PRODUCTS_PER_PAGE_COOKIE_NAME, true]
        ]);
        $this->stubRequest->method('getCookieValue')->willReturnMap([
            [ProductListingRequestHandler::PRODUCTS_PER_PAGE_COOKIE_NAME, $productsPerPage]
        ]);

        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $addSnippetsToPageSpy = $this->createAddedSnippetsSpy();

        $this->requestHandler->process($this->stubRequest);

        $this->assertDynamicSnippetWasAddedToPageBuilder($addSnippetsToPageSpy, $snippetCode, $expectedSnippetContent);
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
        $this->setExpectedNumberOfProductPerPage($this->testDefaultNumberOfProductsPerPage);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testNumberOfProductsPerPageStoredInCookieIsRequestedFromDataPool()
    {
        $productsPerPage = 2;

        $this->stubRequest->method('hasCookie')->willReturnMap([
            [ProductListingRequestHandler::PRODUCTS_PER_PAGE_COOKIE_NAME, true]
        ]);
        $this->stubRequest->method('getCookieValue')->willReturnMap([
            [ProductListingRequestHandler::PRODUCTS_PER_PAGE_COOKIE_NAME, $productsPerPage]
        ]);

        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $this->setExpectedNumberOfProductPerPage($productsPerPage);

        $this->requestHandler->process($this->stubRequest);
    }

    /**
     * @runInSeparateProcess
     */
    public function testNumberOfProductsPerPageFromQueryStringIsRequestedFromDataPool()
    {
        $productsPerPage = 3;

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubHttpRequest->method('getUrlPathRelativeToWebFront')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        $stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $stubHttpRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingRequestHandler::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME, $productsPerPage],
            [ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME, 'whatever'],
        ]);

        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $this->setExpectedNumberOfProductPerPage($productsPerPage);

        $this->requestHandler->process($stubHttpRequest);
    }

    /**
     * @runInSeparateProcess
     * @requires extension xdebug
     */
    public function testProductsPrePageCookieIsSetIfCorrespondingQueryParameterIsPresent()
    {
        $selectedNumberOfProductsPerPage = 2;

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubHttpRequest->method('getUrlPathRelativeToWebFront')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        $stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $stubHttpRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingRequestHandler::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME, $selectedNumberOfProductsPerPage],
            [ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME, 'whatever'],
        ]);

        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $this->requestHandler->process($stubHttpRequest);

        $headers = xdebug_get_headers();
        $expectedCookie = sprintf(
            'Set-Cookie: %s=%s; expires=%s; Max-Age=%s',
            ProductListingRequestHandler::PRODUCTS_PER_PAGE_COOKIE_NAME,
            $selectedNumberOfProductsPerPage,
            gmdate('D, d-M-Y H:i:s T', time() + ProductListingRequestHandler::PRODUCTS_PER_PAGE_COOKIE_TTL),
            ProductListingRequestHandler::PRODUCTS_PER_PAGE_COOKIE_TTL
        );

        $this->assertContains($expectedCookie, $headers);
    }

    public function testExceptionIsThrownIfNoSortOrderConfigIsSelected()
    {
        $this->setExpectedException(NoSelectedSortOrderException::class);

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $stubSnippetKeyGeneratorLocator = $this->createStubSnippetKeyGeneratorLocator();
        $testFilterNavigationConfig = ['foo' => []];
        $productsPerPage = ProductsPerPage::create([1, 2, 3], $this->testDefaultNumberOfProductsPerPage);

        /** @var SortOrderConfig|\PHPUnit_Framework_MockObject_MockObject $stubSortOrderConfig */
        $stubSortOrderConfig = $this->getMock(SortOrderConfig::class, [], [], '', false);

        $requestHandler = $this->createRequestHandler(
            $stubContext,
            $this->mockDataPoolReader,
            $this->mockPageBuilder,
            $stubSnippetKeyGeneratorLocator,
            $testFilterNavigationConfig,
            $productsPerPage,
            $stubSortOrderConfig
        );

        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $requestHandler->process($this->stubRequest);
    }

    public function testDefaultSortOrderConfigIsPassedToDataPool()
    {
        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $this->setExpectedSortOrderConfig($this->stubSortOrderConfigs[1]);

        $this->requestHandler->process($this->stubRequest);
    }

    public function testSortOrderConfigBasedOnCookieValueIsPassedToDataPool()
    {
        $attributeCode = 'foo';
        $direction = SortOrderDirection::ASC;

        $this->stubRequest->method('hasCookie')->willReturnMap([
            [ProductListingRequestHandler::SORT_ORDER_COOKIE_NAME, true],
            [ProductListingRequestHandler::SORT_DIRECTION_COOKIE_NAME, true],
        ]);
        $this->stubRequest->method('getCookieValue')->willReturnMap([
            [ProductListingRequestHandler::SORT_ORDER_COOKIE_NAME, $attributeCode],
            [ProductListingRequestHandler::SORT_DIRECTION_COOKIE_NAME, $direction],
        ]);

        $expectedSortOrderConfig = SortOrderConfig::createSelected(
            AttributeCode::fromString($attributeCode),
            SortOrderDirection::create($direction)
        );

        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $this->setExpectedSortOrderConfig($expectedSortOrderConfig);

        $this->requestHandler->process($this->stubRequest);
    }

    /**
     * @runInSeparateProcess
     */
    public function testSortOrderAndDirectionFromQueryStringArePassedToDataPool()
    {
        $attributeCode = 'foo';
        $direction = SortOrderDirection::ASC;

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubHttpRequest->method('getUrlPathRelativeToWebFront')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        $stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $stubHttpRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingRequestHandler::SORT_ORDER_QUERY_PARAMETER_NAME, $attributeCode],
            [ProductListingRequestHandler::SORT_DIRECTION_QUERY_PARAMETER_NAME, $direction],
            [ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME, 'whatever'],
        ]);

        $expectedSortOrderConfig = SortOrderConfig::createSelected(
            AttributeCode::fromString($attributeCode),
            SortOrderDirection::create($direction)
        );

        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $this->setExpectedSortOrderConfig($expectedSortOrderConfig);

        $this->requestHandler->process($stubHttpRequest);
    }

    /**
     * @runInSeparateProcess
     * @requires extension xdebug
     */
    public function testSortOrderAndDirectionsCookiesAreSetIfCorrespondingQueryParameterIsPresent()
    {
        $attributeCode = 'foo';
        $direction = SortOrderDirection::ASC;

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubHttpRequest->method('getUrlPathRelativeToWebFront')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        $stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $stubHttpRequest->method('getQueryParameter')->willReturnMap([
            [ProductListingRequestHandler::SORT_ORDER_QUERY_PARAMETER_NAME, $attributeCode],
            [ProductListingRequestHandler::SORT_DIRECTION_QUERY_PARAMETER_NAME, $direction],
            [ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME, 'whatever'],
        ]);

        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $this->requestHandler->process($stubHttpRequest);

        $headers = xdebug_get_headers();

        $expectedSortOrderCookie = sprintf(
            'Set-Cookie: %s=%s; expires=%s; Max-Age=%s',
            ProductListingRequestHandler::SORT_ORDER_COOKIE_NAME,
            $attributeCode,
            gmdate('D, d-M-Y H:i:s T', time() + ProductListingRequestHandler::SORT_ORDER_COOKIE_TTL),
            ProductListingRequestHandler::SORT_ORDER_COOKIE_TTL
        );

        $expectedSortDirectionCookie = sprintf(
            'Set-Cookie: %s=%s; expires=%s; Max-Age=%s',
            ProductListingRequestHandler::SORT_DIRECTION_COOKIE_NAME,
            $direction,
            gmdate('D, d-M-Y H:i:s T', time() + ProductListingRequestHandler::SORT_DIRECTION_COOKIE_TTL),
            ProductListingRequestHandler::SORT_DIRECTION_COOKIE_TTL
        );

        $this->assertContains($expectedSortOrderCookie, $headers);
        $this->assertContains($expectedSortDirectionCookie, $headers);
    }

    public function testSortOrderConfigSnippetIsAddedToPageBuilder()
    {
        $snippetCode = 'sort_order_config';

        $this->prepareMockDataPoolReaderWithDefaultStubSearchDocumentCollection();
        $addSnippetsToPageSpy = $this->createAddedSnippetsSpy();

        $this->requestHandler->process($this->stubRequest);

        $this->assertDynamicSnippetWithAnyValueWasAddedToPageBuilder($addSnippetsToPageSpy, $snippetCode);
    }
}
