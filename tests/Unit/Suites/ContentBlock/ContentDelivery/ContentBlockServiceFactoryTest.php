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
 * @covers  \LizardsAndPumpkins\ContentBlock\ContentDelivery\ContentBlockServiceFactory
 * @uses    \LizardsAndPumpkins\DataPool\KeyGenerator\CompositeSnippetKeyGeneratorLocatorStrategy
 * @uses    \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses    \LizardsAndPumpkins\ProductListing\Import\ProductListingContentBlockSnippetKeyGeneratorLocatorStrategy
 * @uses    \LizardsAndPumpkins\ContentBlock\ContentDelivery\ContentBlockApiV2GetRequestHandler
 * @uses    \LizardsAndPumpkins\ContentBlock\ContentDelivery\ContentBlockService
 * @uses    \LizardsAndPumpkins\Util\Factory\FactoryWithCallbackTrait
 * @uses    \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses    \LizardsAndPumpkins\Context\DataVersion\ContextVersion
 * @uses    \LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator
 * @uses    \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses    \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses    \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses    \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses    \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses    \LizardsAndPumpkins\RestApi\RestApiFactory
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