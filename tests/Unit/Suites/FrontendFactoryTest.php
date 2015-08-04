<?php

namespace Brera;

use Brera\Api\ApiRouter;
use Brera\Content\ContentBlocksApiV1PutRequestHandler;
use Brera\Context\Context;
use Brera\Product\CatalogImportApiV1PutRequestHandler;
use Brera\Product\ProductDetailViewRouter;
use Brera\Product\ProductListingRouter;

/**
 * @covers \Brera\FrontendFactory
 * @covers \Brera\FactoryTrait
 * @uses   \Brera\MasterFactoryTrait
 * @uses   \Brera\SampleMasterFactory
 * @uses   \Brera\IntegrationTestFactory
 * @uses   \Brera\CommonFactory
 * @uses   \Brera\Content\ContentBlocksApiV1PutRequestHandler
 * @uses   \Brera\Context\ContextBuilder
 * @uses   \Brera\Product\CatalogImportApiV1PutRequestHandler
 * @uses   \Brera\Product\ProductDetailViewRouter
 * @uses   \Brera\Product\ProductDetailViewRequestHandlerBuilder
 * @uses   \Brera\Product\ProductListingRouter
 * @uses   \Brera\Product\ProductListingRequestHandlerBuilder
 * @uses   \Brera\Product\MultipleProductStockQuantityApiV1PutRequestHandler
 * @uses   \Brera\DataPool\DataPoolReader
 * @uses   \Brera\DataVersion
 * @uses   \Brera\Api\ApiRouter
 * @uses   \Brera\Api\ApiRequestHandlerChain
 * @uses   \Brera\SnippetKeyGeneratorLocator
 * @uses   \Brera\GenericSnippetKeyGenerator
 * @uses   \Brera\PageBuilder
 * @uses   \Brera\PageTemplatesApiV1PutRequestHandler
 * @uses   \Brera\RootSnippetSourceListBuilder
 * @uses   \Brera\Utils\Directory
 */
class FrontendFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FrontendFactory
     */
    private $frontendFactory;

    public function setUp()
    {
        $masterFactory = new SampleMasterFactory();
        $masterFactory->register(new IntegrationTestFactory());
        $masterFactory->register(new CommonFactory());
        $this->frontendFactory = new FrontendFactory();
        $masterFactory->register($this->frontendFactory);
    }

    public function testCatalogImportApiRequestHandlerIsReturned()
    {
        $result = $this->frontendFactory->createCatalogImportApiV1PutRequestHandler();
        $this->assertInstanceOf(CatalogImportApiV1PutRequestHandler::class, $result);
    }

    public function testContentBlocksApiRequestHandlerIsReturned()
    {
        $result = $this->frontendFactory->createContentBlocksApiV1PutRequestHandler();
        $this->assertInstanceOf(ContentBlocksApiV1PutRequestHandler::class, $result);
    }

    public function testApiRouterIsReturned()
    {
        $result = $this->frontendFactory->createApiRouter();
        $this->assertInstanceOf(ApiRouter::class, $result);
    }

    public function testProductDetailViewRouterIsReturned()
    {
        $result = $this->frontendFactory->createProductDetailViewRouter();
        $this->assertInstanceOf(ProductDetailViewRouter::class, $result);
    }

    public function testProductListingRouterIsReturned()
    {
        $result = $this->frontendFactory->createProductListingRouter();
        $this->assertInstanceOf(ProductListingRouter::class, $result);
    }

    public function testSameKeyGeneratorLocatorIsReturnedViaGetter()
    {
        $result1 = $this->frontendFactory->getSnippetKeyGeneratorLocator();
        $result2 = $this->frontendFactory->getSnippetKeyGeneratorLocator();
        $this->assertInstanceOf(SnippetKeyGeneratorLocator::class, $result1);
        $this->assertSame($result1, $result2);
    }

    public function testItReturnsASetContext()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $this->frontendFactory->setContext($stubContext);
        $this->assertSame($stubContext, $this->frontendFactory->getContext());
    }

    public function testItThrowsAnExceptionIfTheContextIsRetrievedBeforeItIsSet()
    {
        $this->setExpectedException(
            ContextNotSetOnFactoryException::class,
            'The context was not set on the factory. Is the bootstrap complete?'
        );
        $this->frontendFactory->getContext();
    }
}
