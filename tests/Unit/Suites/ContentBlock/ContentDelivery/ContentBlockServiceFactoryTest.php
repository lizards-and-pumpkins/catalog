<?php
declare(strict_types=1);

namespace LizardsAndPumpkins\ContentBlock\ContentDelivery;

use LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator;
use LizardsAndPumpkins\RestApi\RestApiFactory;
use LizardsAndPumpkins\UnitTestFactory;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\FactoryWithCallback;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

/**
 * Class ContentBlockServiceFactoryTest
 *
 * @package LizardsAndPumpkins\ContentBlock\ContentDelivery
 * @covers \LizardsAndPumpkins\ContentBlock\ContentDelivery\ContentBlockServiceFactory
 */
class ContentBlockServiceFactoryTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var ContentBlockServiceFactory
     */
    protected $factory;

    public function setUp()
    {
        $masterFactory = new CatalogMasterFactory();
        $masterFactory->register(new CommonFactory());
        $masterFactory->register(new RestApiFactory());
        $masterFactory->register(new UnitTestFactory($this));

        $this->factory = new ContentBlockServiceFactory();

        $masterFactory->register($this->factory);

        parent::setUp();
    }

    public function testImplementsFactoryWithCallback()
    {
        $this->assertInstanceOf(FactoryWithCallback::class, $this->factory);
    }

    /**
     *
     */
    public function testRegistersApiHandler()
    {
        $apiVersion = 2;
        $apiRequestHandlerLocatorStub = $this->createMock(ApiRequestHandlerLocator::class);

        /** @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject $masterFactory */
        $masterFactory = $this->getMockBuilder(MasterFactory::class)
                              ->setMethods(array_merge(get_class_methods(MasterFactory::class), ['getApiRequestHandlerLocator']))
                              ->getMock();
        $masterFactory->method('getApiRequestHandlerLocator')
                      ->willReturn($apiRequestHandlerLocatorStub);

        $apiRequestHandlerLocatorStub->expects($this->once())
                                     ->method('register')
                                     ->with('get_content_block', $apiVersion, $this->isInstanceOf(\Closure::class));

        $this->factory->factoryRegistrationCallback($masterFactory);
    }

    /**
     *
     */
    public function testCreateContentBlockApiV2GetRequestHandler()
    {
        $this->assertInstanceOf(ContentBlockApiV2GetRequestHandler::class, $this->factory->createContentBlockApiV2GetRequestHandler());
    }
}