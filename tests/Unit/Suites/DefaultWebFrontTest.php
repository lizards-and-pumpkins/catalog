<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\KeyNotFoundException;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\HttpUnknownMethodRequest;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Http\Routing\ResourceNotFoundRequestHandler;
use LizardsAndPumpkins\Http\Routing\UnknownHttpRequestMethodHandler;
use LizardsAndPumpkins\Http\Routing\WebRequestHandlerLocator;
use LizardsAndPumpkins\Import\Exception\MalformedMetaSnippetException;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchRequestHandler;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DefaultWebFront
 * @covers \LizardsAndPumpkins\Http\WebFront
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 * @uses   \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\FrontendFactory
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV2PutRequestHandler
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailViewRequestHandler
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageContentBuilder
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingPageRequest
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductListingRequestHandler
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductSearchRequestHandler
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductsPerPage
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPrices
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\PricesJsonSnippetTransformation
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\ProductJsonSnippetTransformation
 * @uses   \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationTypeCode
 * @uses   \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsLocator
 * @uses   \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsService
 * @uses   \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 * @uses   \LizardsAndPumpkins\UnitTestFactory
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Util\Config\EnvironmentConfigReader
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\FrontendFactory
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\GenericPageBuilder
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\Import\CatalogImport
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\RestApi\CatalogImportApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator
 * @uses   \LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToProductBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\QueueImportCommands
 * @uses   \LizardsAndPumpkins\Import\Product\ProductImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\Listing\ProductListingImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\CompositeSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\RegistrySnippetKeyGeneratorLocatorStrategy
 * @uses   \LizardsAndPumpkins\Http\Routing\ResourceNotFoundRequestHandler
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\DataVersion\ContextVersion
 * @uses   \LizardsAndPumpkins\Context\ContextSource
 * @uses   \LizardsAndPumpkins\RestApi\RestApiRequestHandlerLocator
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer
 * @uses   \LizardsAndPumpkins\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\Util\FileSystem\Directory
 */
class DefaultWebFrontTest extends TestCase
{
    /**
     * @var DefaultWebFront
     */
    private $webFront;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @var UrlToWebsiteMap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubUrlToWebsiteMap;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDataPoolReader;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dummyContext;

    /**
     * @var WebRequestHandlerLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubWebRequestHandlerLocator;

    /**
     * @return MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubMasterFactory()
    {
        $stubFactoryMethods = array_merge(
            get_class_methods(MasterFactory::class),
            ['createContext', 'createUrlToWebsiteMap', 'createDataPoolReader', 'createWebRequestHandlerLocator']
        );

        /** @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject $stubMasterFactory */
        $stubMasterFactory = $this->getMockBuilder(MasterFactory::class)->setMethods($stubFactoryMethods)->getMock();

        $this->dummyContext = $this->createMock(Context::class);
        $this->stubUrlToWebsiteMap = $this->createMock(UrlToWebsiteMap::class);
        $this->stubDataPoolReader = $this->createMock(DataPoolReader::class);
        $this->stubWebRequestHandlerLocator = $this->createMock(WebRequestHandlerLocator::class);

        $stubMasterFactory->method('createContext')->willReturn($this->dummyContext);
        $stubMasterFactory->method('createUrlToWebsiteMap')->willReturn($this->stubUrlToWebsiteMap);
        $stubMasterFactory->method('createDataPoolReader')->willReturn($this->stubDataPoolReader);
        $stubMasterFactory->method('createWebRequestHandlerLocator')->willReturn($this->stubWebRequestHandlerLocator);

        return $stubMasterFactory;
    }

    /**
     * @return HttpRequestHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubRequestHandler()
    {
        $stubHttpRequestHandler = $this->createMock(HttpRequestHandler::class);
        $stubHttpRequestHandler->method('process')->willReturn($this->createMock(HttpResponse::class));

        return $stubHttpRequestHandler;
    }

    final protected function setUp()
    {
        $this->stubRequest = $this->createMock(HttpRequest::class);
        $stubMasterFactory = $this->createStubMasterFactory();

        $this->webFront = new TestDefaultWebFront($this->stubRequest, $stubMasterFactory, new UnitTestFactory($this));
    }

    public function testThrowsAnExceptionIfMetaSnippetHasNoRequestHandlerCode()
    {
        $this->expectException(MalformedMetaSnippetException::class);
        $this->webFront->run();
    }

    public function testTriggersUnknownHttpRequestMethodHandlerForUnknownHttpMethods()
    {
        /** @var HttpUnknownMethodRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequest */
        $stubRequest = $this->createMock(HttpUnknownMethodRequest::class);
        $stubMasterFactory = $this->createStubMasterFactory();

        $this->stubWebRequestHandlerLocator->expects($this->once())->method('getRequestHandlerForCode')
            ->with(UnknownHttpRequestMethodHandler::CODE);

        (new TestDefaultWebFront($stubRequest, $stubMasterFactory, new UnitTestFactory($this)))->run();
    }

    public function testTriggersResourceNotFoundRequestHandlerIfMetaSnippetDoesNotExist()
    {
        $this->stubDataPoolReader->method('getPageMetaSnippet')->willThrowException(new KeyNotFoundException());

        $this->stubWebRequestHandlerLocator->expects($this->once())->method('getRequestHandlerForCode')
            ->with(ResourceNotFoundRequestHandler::CODE)->willReturn($this->getStubRequestHandler());

        $this->webFront->run();
    }

    public function testTriggersResourceNotFoundRequestHandlerIfSearchResultsPageIsRequestedWithInvalidMethod()
    {
        $this->stubDataPoolReader->method('getPageMetaSnippet')->willReturn(
            json_encode([PageMetaInfoSnippetContent::KEY_HANDLER_CODE => ProductSearchRequestHandler::CODE])
        );

        $this->stubWebRequestHandlerLocator->expects($this->once())->method('getRequestHandlerForCode')
            ->with(ResourceNotFoundRequestHandler::CODE)->willReturn($this->getStubRequestHandler());

        $this->webFront->run();
    }

    public function testTriggersResourceNotFoundRequestHandlerIfSearchResultsPageIsRequestedWithoutQueryParameter()
    {
        $this->stubDataPoolReader->method('getPageMetaSnippet')->willReturn(
            json_encode([PageMetaInfoSnippetContent::KEY_HANDLER_CODE => ProductSearchRequestHandler::CODE])
        );

        $this->stubWebRequestHandlerLocator->expects($this->once())->method('getRequestHandlerForCode')
            ->with(ResourceNotFoundRequestHandler::CODE)->willReturn($this->getStubRequestHandler());

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);

        $this->webFront->run();
    }

    public function testTriggersResourceNotFoundRequestHandlerIfSearchResultsPageIsRequestedWithEmptyQueryParameter()
    {
        $this->stubDataPoolReader->method('getPageMetaSnippet')->willReturn(
            json_encode([PageMetaInfoSnippetContent::KEY_HANDLER_CODE => ProductSearchRequestHandler::CODE])
        );

        $this->stubWebRequestHandlerLocator->expects($this->once())->method('getRequestHandlerForCode')
            ->with(ResourceNotFoundRequestHandler::CODE)->willReturn($this->getStubRequestHandler());

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('hasQueryParameter')->with(ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME)
            ->willReturn(true);

        $this->webFront->run();
    }

    public function testTriggersProductSearchRequestHandler()
    {
        $this->stubDataPoolReader->method('getPageMetaSnippet')->willReturn(
            json_encode([PageMetaInfoSnippetContent::KEY_HANDLER_CODE => ProductSearchRequestHandler::CODE])
        );

        $this->stubWebRequestHandlerLocator->expects($this->once())->method('getRequestHandlerForCode')
            ->with(ProductSearchRequestHandler::CODE)->willReturn($this->getStubRequestHandler());

        $this->stubRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->stubRequest->method('hasQueryParameter')->with(ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME)
            ->willReturn(true);
        $this->stubRequest->method('getQueryParameter')->with(ProductSearchRequestHandler::QUERY_STRING_PARAMETER_NAME)
            ->willReturn('foo');

        $this->webFront->run();
    }

    public function testSendMethodOfResponseIsCalled()
    {
        $urlKey = 'bar';
        $url = 'http://example.com/' . $urlKey;
        $requestHandlerCode = 'qux';

        $stubUrl = $this->createMock(HttpUrl::class);
        $stubUrl->method('__toString')->willReturn($url);

        $snippetContents = json_encode([PageMetaInfoSnippetContent::KEY_HANDLER_CODE => $requestHandlerCode]);

        $mockHttpResponse = $this->createMock(HttpResponse::class);

        $stubHttpRequestHandler = $this->createMock(HttpRequestHandler::class);
        $stubHttpRequestHandler->method('process')->willReturn($mockHttpResponse);

        $this->stubRequest->method('getUrl')->willReturn($stubUrl);
        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->with($url)->willReturn($urlKey);
        $this->stubDataPoolReader->method('getPageMetaSnippet')->with($urlKey, $this->dummyContext)
            ->willReturn($snippetContents);
        $this->stubWebRequestHandlerLocator->method('getRequestHandlerForCode')->with($requestHandlerCode)
            ->willReturn($stubHttpRequestHandler);

        $mockHttpResponse->expects($this->once())->method('send');

        $this->webFront->run();
    }
}
