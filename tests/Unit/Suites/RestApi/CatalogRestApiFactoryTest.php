<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Core\Factory\Factory;
use LizardsAndPumpkins\DataPool\DataVersion\RestApi\CurrentVersionApiV1GetRequestHandler;
use LizardsAndPumpkins\DataPool\DataVersion\RestApi\CurrentVersionApiV1PutRequestHandler;
use LizardsAndPumpkins\Http\UrlToWebsiteMapBasedUrlParser;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV2PutRequestHandler;
use LizardsAndPumpkins\Import\RestApi\CatalogImportApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\RestApi\CatalogImportApiV2PutRequestHandler;
use LizardsAndPumpkins\Import\RestApi\ProductImportApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\RestApi\TemplateApiV1GetRequestHandler;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV2PutRequestHandler;
use LizardsAndPumpkins\Import\XmlParser\ProductJsonToXml;
use LizardsAndPumpkins\UnitTestFactory;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\RestApi\CatalogRestApiFactory
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
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator
 * @uses   \LizardsAndPumpkins\RestApi\ApiRouter
 * @uses   \LizardsAndPumpkins\Util\Config\EnvironmentConfigReader
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\Core\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Core\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\RestApi\CurrentVersionApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\DataPool\DataVersion\RestApi\CurrentVersionApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\DataVersion\ContextVersion
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolWriter
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\Import\GenericSnippetProjector
 * @uses   \LizardsAndPumpkins\Import\RestApi\TemplateApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\TemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductDetail\Import\ProductDetailTemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductSearchResultMetaSnippetRenderer
 * @uses   \LizardsAndPumpkins\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 * @uses   \LizardsAndPumpkins\Http\UrlToWebsiteMapBasedUrlParser
 */
class CatalogRestApiFactoryTest extends TestCase
{
    /**
     * @var RestApiFactory
     */
    private $factory;

    public function assertApiRequestHandlerIsRegistered(ApiRequestHandlerLocator $locator, string $code, int $version): void
    {
        $handler = $locator->getApiRequestHandler($code, $version);
        $message = sprintf('No API request handler "%s" for version "%s" registered', $code, $version);
        $this->assertNotInstanceOf(NullApiRequestHandler::class, $handler, $message);
    }

    final protected function setUp(): void
    {
        $masterFactory = new CatalogMasterFactory();
        $masterFactory->register(new CommonFactory());
        $masterFactory->register(new UnitTestFactory($this));

        $this->factory = new CatalogRestApiFactory();

        $masterFactory->register($this->factory);
    }

    public function testFactoryInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(Factory::class, $this->factory);
    }

    public function testApiRequestHandlerLocatorIsReturned(): void
    {
        $result = $this->factory->getApiRequestHandlerLocator();
        $this->assertInstanceOf(ApiRequestHandlerLocator::class, $result);
    }

    public function testUrlToWebsiteMapBasedUrlParserIsReturned(): void
    {
        $this->assertInstanceOf(UrlToWebsiteMapBasedUrlParser::class, $this->factory->createHttpUrlParser());
    }

    /**
     * @dataProvider registeredRequestHandlerProvider
     */
    public function testRegistersExpectedHandlersWithApiRouter(string $code, int $version): void
    {
        $locator = $this->factory->getApiRequestHandlerLocator();

        $this->assertApiRequestHandlerIsRegistered($locator, $code, $version);
    }

    /**
     * @return array[]
     */
    public function registeredRequestHandlerProvider(): array
    {
        return [
            'put_catalog_import v1' => ['put_catalog_import', 1],
            'put_catalog_import v2' => ['put_catalog_import', 2],
            'put_product_import v1' => ['put_product_import', 1],
            'put_content_blocks v1' => ['put_content_blocks', 1],
            'put_content_blocks v2' => ['put_content_blocks', 2],
            'put_templates v1' => ['put_templates', 1],
            'put_templates v2' => ['put_templates', 2],
            'get_current_version v1' => ['get_current_version', 1],
            'get_current_version v2' => ['put_current_version', 1],
            'get_templates v1' => ['get_templates', 1],
        ];
    }

    public function testCatalogImportV1ApiRequestHandlerIsReturned(): void
    {
        $result = $this->factory->createCatalogImportApiV1PutRequestHandler();
        $this->assertInstanceOf(CatalogImportApiV1PutRequestHandler::class, $result);
    }

    public function testReturnsCatalogImportV2ApiRequestHandler(): void
    {
        $result = $this->factory->createCatalogImportApiV2PutRequestHandler();
        $this->assertInstanceOf(CatalogImportApiV2PutRequestHandler::class, $result);
    }

    public function testContentBlocksApiV1RequestHandlerIsReturned(): void
    {
        $result = $this->factory->createContentBlocksApiV1PutRequestHandler();
        $this->assertInstanceOf(ContentBlocksApiV1PutRequestHandler::class, $result);
    }

    public function testContentBlocksApiV2RequestHandlerIsReturned(): void
    {
        $result = $this->factory->createContentBlocksApiV2PutRequestHandler();
        $this->assertInstanceOf(ContentBlocksApiV2PutRequestHandler::class, $result);
    }

    public function testReturnsCurrentVersionApiV1GetRequestHandler(): void
    {
        $result = $this->factory->createCurrentVersionApiV1GetRequestHandler();
        $this->assertInstanceOf(CurrentVersionApiV1GetRequestHandler::class, $result);
    }

    public function testReturnsCurrentVersionApiV1PutRequestHandler(): void
    {
        $result = $this->factory->createCurrentVersionApiV1PutRequestHandler();
        $this->assertInstanceOf(CurrentVersionApiV1PutRequestHandler::class, $result);
    }

    public function testReturnsTemplatesApiV1PutRequestHandler(): void
    {
        $result = $this->factory->createTemplatesApiV1PutRequestHandler();
        $this->assertInstanceOf(TemplatesApiV1PutRequestHandler::class, $result);
    }

    public function testReturnsTemplatesApiV2PutRequestHandler(): void
    {
        $result = $this->factory->createTemplatesApiV2PutRequestHandler();
        $this->assertInstanceOf(TemplatesApiV2PutRequestHandler::class, $result);
    }

    public function testReturnsProductImportApiV1PutRequestHandler(): void
    {
        $result = $this->factory->createProductImportApiV1PutRequestHandler();
        $this->assertInstanceOf(ProductImportApiV1PutRequestHandler::class, $result);
    }

    public function testReturnsProductJsonToXml(): void
    {
        $result = $this->factory->createProductJsonToXml();
        $this->assertInstanceOf(ProductJsonToXml::class, $result);
    }

    public function testReturnsTemplateApiV1GetRequestHandler(): void
    {
        $result = $this->factory->createTemplateApiV1GetRequestHandler();
        $this->assertInstanceOf(TemplateApiV1GetRequestHandler::class, $result);
    }
}
