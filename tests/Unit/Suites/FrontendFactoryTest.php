<?php

namespace Brera;

use Brera\Api\ApiRouter;
use Brera\Context\Context;
use Brera\Http\HttpUrl;
use Brera\Product\CatalogImportApiRequestHandler;
use Brera\Product\ProductDetailViewRouter;

/**
 * @covers \Brera\FrontendFactory
 * @covers \Brera\FactoryTrait
 * @uses   \Brera\MasterFactoryTrait
 * @uses   \Brera\PoCMasterFactory
 * @uses   \Brera\IntegrationTestFactory
 * @uses   \Brera\CommonFactory
 * @uses   \Brera\Product\ProductDetailViewRouter
 * @uses   \Brera\Product\ProductDetailViewRequestHandlerBuilder
 * @uses   \Brera\DataPool\DataPoolReader
 * @uses   \Brera\Api\ApiRouter
 * @uses   \Brera\Api\ApiRequestHandlerChain
 * @uses   \Brera\SnippetKeyGeneratorLocator
 * @uses   \Brera\GenericSnippetKeyGenerator
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
        $stubHttpUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $stubContext = $this->getMock(Context::class);
        $result = $this->frontendFactory->createUrlKeyRouter($stubHttpUrl, $stubContext);
        $this->assertInstanceOf(ProductDetailViewRouter::class, $result);
    }

    /**
     * @test
     */
    public function itShouldAlwaysReturnTheSameKeyGeneratorLocatorViaGetter()
    {
        $result1 = $this->frontendFactory->getSnippetKeyGeneratorLocator();
        $result2 = $this->frontendFactory->getSnippetKeyGeneratorLocator();
        $this->assertInstanceOf(SnippetKeyGeneratorLocator::class, $result1);
        $this->assertSame($result1, $result2);
    }
}
