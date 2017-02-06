<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\DataPool\DataVersion\RestApi\CurrentVersionApiV1GetRequestHandler;
use LizardsAndPumpkins\DataPool\DataVersion\RestApi\CurrentVersionApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV2PutRequestHandler;
use LizardsAndPumpkins\Import\RestApi\CatalogImportApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\RestApi\CatalogImportApiV2PutRequestHandler;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV2PutRequestHandler;
use LizardsAndPumpkins\UnitTestFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\RestApi\RestApiFactory
 * @uses   \LizardsAndPumpkins\Import\CatalogImport
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV2PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToProductBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\Listing\ProductListingImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\ProductImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator
 * @uses   \LizardsAndPumpkins\Import\Product\QueueImportCommands
 * @uses   \LizardsAndPumpkins\Import\RestApi\CatalogImportApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\RestApi\CatalogImportApiV2PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV2PutRequestHandler
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator
 * @uses   \LizardsAndPumpkins\RestApi\ApiRouter
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

    public function assertApiRequestHandlerIsRegistered(ApiRequestHandlerLocator $locator, string $code, int $version)
    {
        $handler = $locator->getApiRequestHandler($code, $version);
        $message = sprintf('No API request handler "%s" for version "%s" registered', $code, $version);
        $this->assertNotInstanceOf(NullApiRequestHandler::class, $handler, $message);
    }

    public function setUp()
    {
        $masterFactory = new SampleMasterFactory();
        $masterFactory->register(new CommonFactory());
        $masterFactory->register(new UnitTestFactory($this));

        $this->factory = new RestApiFactory();

        $masterFactory->register($this->factory);
    }

    public function testFactoryInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Factory::class, $this->factory);
    }

    public function testApiRequestHandlerLocatorIsReturned()
    {
        $result = $this->factory->getApiRequestHandlerLocator();
        $this->assertInstanceOf(ApiRequestHandlerLocator::class, $result);
    }

    public function testApiRouterIsReturned()
    {
        $result = $this->factory->createApiRouter();
        $this->assertInstanceOf(ApiRouter::class, $result);
    }

    /**
     * @dataProvider registeredRequestHandlerProvider
     */
    public function testRegistersExpectedHandlersWithApiRouter($code, $version)
    {
        $locator = $this->factory->getApiRequestHandlerLocator();

        $this->assertApiRequestHandlerIsRegistered($locator, $code, $version);
    }

    public function registeredRequestHandlerProvider(): array
    {
        return [
            'put_catalog_import v1'  => ['put_catalog_import', 1],
            'put_catalog_import v2'  => ['put_catalog_import', 2],
            'put_content_blocks v1'  => ['put_content_blocks', 1],
            'put_templates v1'       => ['put_templates', 1],
            'put_templates v2'       => ['put_templates', 2],
            'get_current_version v1' => ['get_current_version', 1],
            'get_current_version v2' => ['put_current_version', 1],
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

    public function testContentBlocksApiRequestHandlerIsReturned()
    {
        $result = $this->factory->createContentBlocksApiV1PutRequestHandler();
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
}
