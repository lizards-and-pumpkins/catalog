<?php

namespace Brera;

use Brera\Api\ApiRouter;
use Brera\Environment\Environment;
use Brera\Http\HttpUrl;
use Brera\Product\CatalogImportApiRequestHandler;

/**
 * @covers \Brera\FrontendFactory
 * @covers \Brera\FactoryTrait
 * @uses   \Brera\MasterFactoryTrait
 * @uses   \Brera\PoCMasterFactory
 * @uses   \Brera\IntegrationTestFactory
 * @uses   \Brera\CommonFactory
 * @uses   \Brera\UrlKeyRouter
 * @uses   \Brera\UrlKeyRequestHandlerBuilder
 * @uses   \Brera\DataPool\DataPoolReader
 * @uses   \Brera\Api\ApiRouter
 * @uses   \Brera\Api\ApiRequestHandlerChain
 */
class FrontendFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FrontendFactory
     */
    private $frontendFactory;

    public function setUp()
    {
        $masterFactory = new PoCMasterFactory();
        $masterFactory->register(new IntegrationTestFactory());
        $masterFactory->register(new CommonFactory());
        $this->frontendFactory = new FrontendFactory();
        $masterFactory->register($this->frontendFactory);
    }

    /**
     * @test
     */
    public function itShouldReturnCatalogImportApiRequestHandler()
    {
        $result = $this->frontendFactory->createCatalogImportApiRequestHandler();
        $this->assertInstanceOf(CatalogImportApiRequestHandler::class, $result);
    }

    /**
     * @test
     */
    public function itShouldCreateAnApiRouter()
    {
        $result = $this->frontendFactory->createApiRouter();
        $this->assertInstanceOf(ApiRouter::class, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnAnUrlKeyRouter()
    {
        $stubHttpUrl = $this->getMockBuilder(HttpUrl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubEnvironment = $this->getMock(Environment::class);
        $result = $this->frontendFactory->createUrlKeyRouter($stubHttpUrl, $stubEnvironment);
        $this->assertInstanceOf(UrlKeyRouter::class, $result);
    }
}
