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
use PHPUnit\Framework\TestCase;

/**
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
class ContentBlockServiceFactoryTest extends TestCase
{
    /**
     * @var ContentBlockServiceFactory
     */
    private $factory;

    final protected function setUp()
    {
        $masterFactory = new CatalogMasterFactory();
        $masterFactory->register(new CommonFactory());
        $masterFactory->register(new RestApiFactory());
        $masterFactory->register(new UnitTestFactory($this));

        $this->factory = new ContentBlockServiceFactory();

        $masterFactory->register($this->factory);
    }

    public function testImplementsFactoryWithCallback()
    {
        $this->assertInstanceOf(FactoryWithCallback::class, $this->factory);
    }

    public function testRegistersApiHandler()
    {
        $apiVersion = 2;
        $mockApiRequestHandlerLocator = $this->createMock(ApiRequestHandlerLocator::class);

        /** @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject $stubMasterFactory */
        $stubMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods(array_merge(get_class_methods(MasterFactory::class), ['getApiRequestHandlerLocator']))
            ->getMock();
        $stubMasterFactory->method('getApiRequestHandlerLocator')->willReturn($mockApiRequestHandlerLocator);

        $mockApiRequestHandlerLocator->expects($this->once())->method('register')
            ->with('get_' . ContentBlockApiV2GetRequestHandler::ENDPOINT, $apiVersion);

        $this->factory->factoryRegistrationCallback($stubMasterFactory);
    }

    public function testCreatesContentBlockApiV2GetRequestHandler()
    {
        $this->assertInstanceOf(
            ContentBlockApiV2GetRequestHandler::class,
            $this->factory->createContentBlockApiV2GetRequestHandler()
        );
    }
}
