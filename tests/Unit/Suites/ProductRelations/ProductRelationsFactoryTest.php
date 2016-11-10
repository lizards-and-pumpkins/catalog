<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductRelations;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\ContentDelivery\FrontendFactory;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsApiV1GetRequestHandler;
use LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsLocator;
use LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsService;
use LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsServiceBuilder;
use LizardsAndPumpkins\ProductRelations\ContentDelivery\SameSeriesProductRelations;
use LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator;
use LizardsAndPumpkins\RestApi\RestApiFactory;
use LizardsAndPumpkins\UnitTestFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallback;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;

/**
 * @covers \LizardsAndPumpkins\ProductRelations\ProductRelationsFactory
 * @uses   \LizardsAndPumpkins\Context\DataVersion\ContextVersion
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\FrontendFactory
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\EnrichProductJsonWithPrices
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonServiceBuilder
 * @uses   \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationTypeCode
 * @uses   \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsApiV1GetRequestHandler
 * @uses   \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsLocator
 * @uses   \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsService
 * @uses   \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationsServiceBuilder
 * @uses   \LizardsAndPumpkins\ProductRelations\ContentDelivery\SameSeriesProductRelations
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator
 * @uses   \LizardsAndPumpkins\RestApi\RestApiFactory
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class ProductRelationsFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductRelationsFactory
     */
    private $factory;

    public function setUp()
    {
        $stubRequest = $this->createMock(HttpRequest::class);

        $masterFactory = new SampleMasterFactory();
        $masterFactory->register(new CommonFactory());
        $masterFactory->register(new RestApiFactory());
        $masterFactory->register(new FrontendFactory($stubRequest));
        $masterFactory->register(new UnitTestFactory($this));

        $this->factory = new ProductRelationsFactory();

        $masterFactory->register($this->factory);
    }

    public function testFactoryInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Factory::class, $this->factory);
    }

    public function testFactoryWithCallbackInterfaceIsImplemented()
    {
        $this->assertInstanceOf(FactoryWithCallback::class, $this->factory);
    }

    public function testItCreatesProductRelationsApiV1GetRequestHandler()
    {
        $result = $this->factory->createProductRelationsApiV1GetRequestHandler();
        $this->assertInstanceOf(ProductRelationsApiV1GetRequestHandler::class, $result);
    }

    public function testItReturnsAProductRelationsService()
    {
        $stubContext = $this->createMock(Context::class);
        $result = $this->factory->createProductRelationsService($stubContext);
        $this->assertInstanceOf(ProductRelationsService::class, $result);
    }

    public function testItReturnsAProductRelationsLocator()
    {
        $result = $this->factory->createProductRelationsLocator();
        $this->assertInstanceOf(ProductRelationsLocator::class, $result);
    }

    public function testItReturnsSameSeriesProductRelations()
    {
        $result = $this->factory->createSameSeriesProductRelations();
        $this->assertInstanceOf(SameSeriesProductRelations::class, $result);
    }

    public function testProductRelationsApiEndpointIsRegistered()
    {
        $endpointKey = 'get_products';
        $apiVersion = 1;

        $mockApiRequestHandlerLocator = $this->createMock(ApiRequestHandlerLocator::class);
        $mockApiRequestHandlerLocator->expects($this->once())->method('register')
            ->with($endpointKey, $apiVersion, $this->isInstanceOf(ProductRelationsApiV1GetRequestHandler::class));

        $stubMasterFactory = $this->getMockBuilder(MasterFactory::class)->setMethods(
            ['register', 'getApiRequestHandlerLocator']
        )->getMock();
        $stubMasterFactory->method('getApiRequestHandlerLocator')->willReturn($mockApiRequestHandlerLocator);

        $this->factory->factoryRegistrationCallback($stubMasterFactory);
    }

    public function testProductRelationsServiceBuilderIsReturned()
    {
        $result = $this->factory->createProductRelationsServiceBuilder();
        $this->assertInstanceOf(ProductRelationsServiceBuilder::class, $result);
    }
}
