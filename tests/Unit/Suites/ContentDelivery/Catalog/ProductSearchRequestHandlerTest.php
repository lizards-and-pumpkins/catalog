<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Exception\UnableToHandleRequestException;
use LizardsAndPumpkins\PageBuilder;
use LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetContent;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\SnippetKeyGeneratorLocator;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductSearchRequestHandler
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingRequestHandlerTrait
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductsPerPage
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetContent
 */
class ProductSearchRequestHandlerTest extends AbstractProductListingRequestHandlerTest
{
    /**
     * @var string
     */
    private $testMetaInfoKey = 'stub-meta-info-key';

    /**
     * @return ProductSearchRequestHandler
     */
    private function createRequestHandlerWithDefaultValues()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);

        /** @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject $stubPageBuilder */
        $stubPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);

        $stubSnippetKeyGeneratorLocator = $this->createStubSnippetKeyGeneratorLocator();
        $testFilterNavigationConfig = [];

        /** @var ProductsPerPage|\PHPUnit_Framework_MockObject_MockObject $stubProductsPerPage */
        $stubProductsPerPage = $this->getMock(ProductsPerPage::class, [], [], '', false);;

        $stubDataPoolReader = $this->createStubDataPoolReader();

        return $this->createRequestHandler(
            $stubContext,
            $stubDataPoolReader,
            $stubPageBuilder,
            $stubSnippetKeyGeneratorLocator,
            $testFilterNavigationConfig,
            $stubProductsPerPage
        );
    }

    /**
     * {@inheritdoc}
     */
    final protected function createRequestHandler(
        Context $context,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder,
        SnippetKeyGeneratorLocator $snippetKeyGeneratorLocator,
        array $filterNavigationConfig,
        ProductsPerPage $productsPerPage,
        SortOrderConfig ...$sortOrderConfigs
    ) {
        $stubCriteria = $this->getMock(SearchCriteria::class);

        /** @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject $stubSearchCriteriaBuilder */
        $stubSearchCriteriaBuilder = $this->getMock(SearchCriteriaBuilder::class);
        $stubSearchCriteriaBuilder->method('anyOfFieldsContainString')->willReturn($stubCriteria);

        $testSearchableAttributeCodes = ['foo'];

        return new ProductSearchRequestHandler(
            $context,
            $dataPoolReader,
            $pageBuilder,
            $snippetKeyGeneratorLocator,
            $filterNavigationConfig,
            $productsPerPage,
            $stubSearchCriteriaBuilder,
            $testSearchableAttributeCodes,
            ...$sortOrderConfigs
        );
    }

    /**
     * {@inheritdoc}
     */
    final protected function createStubSnippetKeyGeneratorLocator()
    {
        $stubMetaInfoSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class, [], [], '', false);
        $stubMetaInfoSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->testMetaInfoKey);

        $stubSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $stubSnippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')
            ->willReturn($stubMetaInfoSnippetKeyGenerator);

        return $stubSnippetKeyGeneratorLocator;
    }

    /**
     * {@inheritdoc}
     */
    final protected function createStubDataPoolReader()
    {
        $stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);

        $rootSnippetCode = 'foo';
        $pageSnippetCodes = ['foo'];
        $testMetaInfoSnippetJson = json_encode(ProductSearchResultMetaSnippetContent::create(
            $rootSnippetCode,
            $pageSnippetCodes
        )->getInfo());

        $stubDataPoolReader->method('getSnippets')->willReturn([]);
        $stubDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->testMetaInfoKey, $testMetaInfoSnippetJson]
        ]);

        return $stubDataPoolReader;
    }

    /**
     * {@inheritdoc}
     */
    final protected function createStubRequest()
    {
        $queryString = 'foo';

        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubHttpRequest->method('getUrlPathRelativeToWebFront')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        $stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $stubHttpRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME, $queryString],
        ]);

        return $stubHttpRequest;
    }

    public function testRequestCanNotBeProcessedIfRequestUrlIsNotEqualToSearchPageUrl()
    {
        $requestHandler = $this->createRequestHandlerWithDefaultValues();

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubHttpRequest->method('getUrlPathRelativeToWebFront')->willReturn('foo');
        $stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);

        $this->assertFalse($requestHandler->canProcess($stubHttpRequest));

        return $stubHttpRequest;
    }

    public function testRequestCanNotBeProcessedIfRequestMethodIsNotGet()
    {
        $requestHandler = $this->createRequestHandlerWithDefaultValues();

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubHttpRequest->method('getUrlPathRelativeToWebFront')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        $stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_POST);

        $this->assertFalse($requestHandler->canProcess($stubHttpRequest));
    }

    public function testRequestCanNotBeProcessedIfQueryStringParameterIsNotPresent()
    {
        $requestHandler = $this->createRequestHandlerWithDefaultValues();

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubHttpRequest->method('getUrlPathRelativeToWebFront')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        $stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);

        $this->assertFalse($requestHandler->canProcess($stubHttpRequest));
    }

    public function testRequestCanNotBeProcessedIfQueryStringIsShorterThenMinimalAllowedLength()
    {
        $requestHandler = $this->createRequestHandlerWithDefaultValues();

        $queryString = 'f';

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubHttpRequest->method('getUrlPathRelativeToWebFront')
            ->willReturn(ProductSearchRequestHandler::SEARCH_RESULTS_SLUG);
        $stubHttpRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $stubHttpRequest->method('getQueryParameter')->willReturnMap([
            [ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME, $queryString],
        ]);

        $this->assertFalse($requestHandler->canProcess($stubHttpRequest));
    }

    /**
     * @depends testRequestCanNotBeProcessedIfRequestUrlIsNotEqualToSearchPageUrl
     * @param HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest
     */
    public function testExceptionIsThrownDuringAttemptToProcessInvalidRequest(HttpRequest $stubHttpRequest)
    {
        $requestHandler = $this->createRequestHandlerWithDefaultValues();
        $this->setExpectedException(UnableToHandleRequestException::class);
        $requestHandler->process($stubHttpRequest);
    }
}
