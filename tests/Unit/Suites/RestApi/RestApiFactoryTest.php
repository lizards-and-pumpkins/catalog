<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\DataVersion\RestApi\CurrentVersionApiV1GetRequestHandler;
use LizardsAndPumpkins\DataPool\DataVersion\RestApi\CurrentVersionApiV1PutRequestHandler;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Import\CatalogImport;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV2PutRequestHandler;
use LizardsAndPumpkins\Import\RestApi\CatalogImportApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\RestApi\CatalogImportApiV2PutRequestHandler;
use LizardsAndPumpkins\Import\RestApi\ProductImportApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV2PutRequestHandler;
use LizardsAndPumpkins\Import\XmlParser\ProductJsonToXml;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Util\Config\ConfigReader;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\RestApi\RestApiFactory
 * @uses   \LizardsAndPumpkins\Import\CatalogImport
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV2PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToProductBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\Listing\ProductListingImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\ProductImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator
 * @uses   \LizardsAndPumpkins\Import\Product\QueueImportCommands
 * @uses   \LizardsAndPumpkins\Import\RestApi\CatalogImportApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\RestApi\CatalogImportApiV2PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\RestApi\ProductImportApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV2PutRequestHandler
 * @uses   \LizardsAndPumpkins\RestApi\RestApiRequestHandlerLocator
 * @uses   \LizardsAndPumpkins\Util\Config\EnvironmentConfigReader
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\RestApi\CurrentVersionApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\RestApi\CurrentVersionApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\DataVersion\ContextVersion
 */
class RestApiFactoryTest extends TestCase
{
    /**
     * @var RestApiFactory
     */
    private $factory;

    /**
     * @var UrlToWebsiteMap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubUrlToWebsiteMap;

    /**
     * @return MasterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubMasterFactory()
    {
        $apiRequestHandlerMethods = [
            'createCatalogImportApiV1PutRequestHandler',
            'createProductImportApiV1PutRequestHandler',
            'createContentBlocksApiV1PutRequestHandler',
            'createTemplatesApiV1PutRequestHandler',
            'createCurrentVersionApiV1GetRequestHandler',
            'createCurrentVersionApiV1PutRequestHandler',
        ];

        $stubMasterFactoryMethods = [
            'createUrlToWebsiteMap',
            'createConfigReader',
            'getCommandQueue',
            'getLogger',
            'getCurrentDataVersion',
            'createContextBuilder',
            'createDataPoolReader',
            'createProductJsonToXml',
            'createCatalogImport',
        ];

        $stubFactoryMethods = array_merge(
            get_class_methods(MasterFactory::class),
            $stubMasterFactoryMethods,
            $apiRequestHandlerMethods
        );

        /** @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject $stubMasterFactory */
        $stubMasterFactory = $this->getMockBuilder(MasterFactory::class)->setMethods($stubFactoryMethods)->getMock();

        $this->stubUrlToWebsiteMap = $this->createMock(UrlToWebsiteMap::class);
        $stubMasterFactory->method('createUrlToWebsiteMap')->willReturn($this->stubUrlToWebsiteMap);

        $dummyConfigReader = $this->createMock(ConfigReader::class);
        $dummyConfigReader->method('get')->with('catalog_import_directory')->willReturn(sys_get_temp_dir());
        $stubMasterFactory->method('createConfigReader')->willReturn($dummyConfigReader);

        $dummyCommandQueue = $this->createMock(CommandQueue::class);
        $stubMasterFactory->method('getCommandQueue')->willReturn($dummyCommandQueue);

        $dummyLogger = $this->createMock(Logger::class);
        $stubMasterFactory->method('getLogger')->willReturn($dummyLogger);

        $dummyContextBuilder = $this->createMock(ContextBuilder::class);
        $stubMasterFactory->method('createContextBuilder')->willReturn($dummyContextBuilder);

        $dummyDataPoolReader = $this->createMock(DataPoolReader::class);
        $stubMasterFactory->method('createDataPoolReader')->willReturn($dummyDataPoolReader);

        $dummyProductJsonToXml = $this->createMock(ProductJsonToXml::class);
        $stubMasterFactory->method('createProductJsonToXml')->willReturn($dummyProductJsonToXml);

        $dummyCatalogImport = $this->createMock(CatalogImport::class);
        $stubMasterFactory->method('createCatalogImport')->willReturn($dummyCatalogImport);

        $stubMasterFactory->method('getCurrentDataVersion')->willReturn('foo');

        every($apiRequestHandlerMethods, function ($method) use ($stubMasterFactory) {
            $stubMasterFactory->method($method)->willReturn($this->createMock(RestApiRequestHandler::class));
        });

        return $stubMasterFactory;
    }

    public function setUp()
    {
        $this->factory = new RestApiFactory();
        $this->factory->setMasterFactory($this->createStubMasterFactory());
    }

    public function testFactoryInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Factory::class, $this->factory);
    }

    public function testApiRequestHandlerLocatorIsReturned()
    {
        $result = $this->factory->getRestApiRequestHandlerLocator();
        $this->assertInstanceOf(RestApiRequestHandlerLocator::class, $result);
    }

    /**
     * @dataProvider registeredRequestHandlerProvider
     */
    public function testRegistersExpectedHandlersWithApiRouter(string $requestMethod, string $endpoint, int $version)
    {
        $locator = $this->factory->getRestApiRequestHandlerLocator();
        $code = sprintf('%s_%s', strtolower($requestMethod), $endpoint);

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->createMock(HttpRequest::class);
        $stubHttpRequest->method('hasHeader')->with('Accept')->willReturn(true);
        $stubHttpRequest->method('getHeader')->with('Accept')
            ->willReturn('application/vnd.lizards-and-pumpkins.foo.v1+json');
        $stubHttpRequest->method('getMethod')->willReturn($requestMethod);

        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')
            ->willReturn(RestApiRequestHandlerLocator::API_URL_PREFIX . '/' . $endpoint);

        $handler = $locator->getApiRequestHandler($stubHttpRequest);
        $message = sprintf('No API request handler "%s" for version "%s" registered', $code, $version);
        $this->assertNotInstanceOf(NullApiRequestHandler::class, $handler, $message);
    }

    /**
     * @return array[]
     */
    public function registeredRequestHandlerProvider() : array
    {
        return [
            'put_catalog_import v1'  => ['PUT', 'catalog_import', 1],
            'put_catalog_import v2'  => ['PUT', 'catalog_import', 2],
            'put_product_import v1'  => ['PUT', 'product_import', 1],
            'put_content_blocks v1'  => ['PUT', 'content_blocks', 1],
            'put_content_blocks v2'  => ['PUT', 'content_blocks', 2],
            'put_templates v1'       => ['PUT', 'templates', 1],
            'put_templates v2'       => ['PUT', 'templates', 2],
            'get_current_version v1' => ['GET', 'current_version', 1],
            'get_current_version v2' => ['PUT', 'current_version', 1],
        ];
    }

    public function testCatalogImportV1ApiRequestHandlerIsReturned()
    {
        $result = $this->factory->createCatalogImportApiV1PutRequestHandler();
        $this->assertInstanceOf(CatalogImportApiV1PutRequestHandler::class, $result);
    }

    public function testReturnsCatalogImportV2ApiRequestHandler()
    {
        $result = $this->factory->createCatalogImportApiV2PutRequestHandler();
        $this->assertInstanceOf(CatalogImportApiV2PutRequestHandler::class, $result);
    }

    public function testContentBlocksApiV1RequestHandlerIsReturned()
    {
        $result = $this->factory->createContentBlocksApiV1PutRequestHandler();
        $this->assertInstanceOf(ContentBlocksApiV1PutRequestHandler::class, $result);
    }

    public function testContentBlocksApiV2RequestHandlerIsReturned()
    {
        $result = $this->factory->createContentBlocksApiV2PutRequestHandler();
        $this->assertInstanceOf(ContentBlocksApiV2PutRequestHandler::class, $result);
    }

    public function testReturnsCurrentVersionApiV1GetRequestHandler()
    {
        $result = $this->factory->createCurrentVersionApiV1GetRequestHandler();
        $this->assertInstanceOf(CurrentVersionApiV1GetRequestHandler::class, $result);
    }

    public function testReturnsCurrentVersionApiV1PutRequestHandler()
    {
        $result = $this->factory->createCurrentVersionApiV1PutRequestHandler();
        $this->assertInstanceOf(CurrentVersionApiV1PutRequestHandler::class, $result);
    }

    public function testReturnsTemplatesApiV1PutRequestHandler()
    {
        $result = $this->factory->createTemplatesApiV1PutRequestHandler();
        $this->assertInstanceOf(TemplatesApiV1PutRequestHandler::class, $result);
    }

    public function testReturnsTemplatesApiV2PutRequestHandler()
    {
        $result = $this->factory->createTemplatesApiV2PutRequestHandler();
        $this->assertInstanceOf(TemplatesApiV2PutRequestHandler::class, $result);
    }

    public function testReturnsProductImportApiV1PutRequestHandler()
    {
        $result = $this->factory->createProductImportApiV1PutRequestHandler();
        $this->assertInstanceOf(ProductImportApiV1PutRequestHandler::class, $result);
    }

    public function testReturnsProductJsonToXml()
    {
        $result = $this->factory->createProductJsonToXml();
        $this->assertInstanceOf(ProductJsonToXml::class, $result);
    }
}
