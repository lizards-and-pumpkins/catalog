<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\DataPool\DataVersion\RestApi\CurrentVersionApiV1GetRequestHandler;
use LizardsAndPumpkins\DataPool\DataVersion\RestApi\CurrentVersionApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\RestApi\CatalogImportApiV1PutRequestHandler;
use LizardsAndPumpkins\Import\RestApi\CatalogImportApiV2PutRequestHandler;
use LizardsAndPumpkins\UnitTestFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;

/**
 * @covers \LizardsAndPumpkins\RestApi\RestApiFactory
 * @uses   \LizardsAndPumpkins\Import\CatalogImport
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\RestApi\ContentBlocksApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToProductBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\Listing\ProductListingImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\ProductImportCommandLocator
 * @uses   \LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator
 * @uses   \LizardsAndPumpkins\Import\Product\QueueImportCommands
 * @uses   \LizardsAndPumpkins\Import\RestApi\CatalogImportApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\RestApi\CatalogImportApiV2PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\Import\TemplatesApiV1PutRequestHandler
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
 */
class RestApiFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RestApiFactory
     */
    private $factory;

    public function assertApiRequestHandlerIsRegistered(ApiRequestHandlerLocator $locator, string $code, int $version)
    {
        $this->assertNotInstanceOf(
            NullApiRequestHandler::class,
            $locator->getApiRequestHandler($code, $version),
            sprintf('No API request handler "%s" for version "%s" registered', $code, $version)
        );
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

    public function testRegistersExpectedHandlersWithApiRouter()
    {
        $locator = $this->factory->getApiRequestHandlerLocator();
        
        $this->assertApiRequestHandlerIsRegistered($locator, 'put_catalog_import', 1);
        $this->assertApiRequestHandlerIsRegistered($locator, 'put_catalog_import', 2);
        $this->assertApiRequestHandlerIsRegistered($locator, 'put_content_blocks', 1);
        $this->assertApiRequestHandlerIsRegistered($locator, 'put_templates', 1);
        $this->assertApiRequestHandlerIsRegistered($locator, 'get_current_version', 1);
        $this->assertApiRequestHandlerIsRegistered($locator, 'put_current_version', 1);
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
        $this->assertInstanceOf(ContentBlocksApiV1PutRequestHandler::class, $result);
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
}
