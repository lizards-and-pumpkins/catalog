<?php

namespace Brera;

use Brera\Api\ApiRouter;
use Brera\Content\ContentBlocksApiV1PutRequestHandler;
use Brera\Context\Context;
use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpsUrl;
use Brera\Product\CatalogImportApiV1PutRequestHandler;
use Brera\Product\ProductDetailViewRouter;
use Brera\Product\ProductListingRouter;
use Brera\Product\ProductSearchResultsRouter;

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
 * @uses   \Brera\Product\ProductDetailViewRequestHandler
 * @uses   \Brera\Product\ProductListingRouter
 * @uses   \Brera\Product\ProductListingRequestHandler
 * @uses   \Brera\Product\ProductSearchRequestHandler
 * @uses   \Brera\Product\ProductSearchResultsRouter
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
 * @uses   \Brera\Http\HttpRequest
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Http\HttpHeaders
 * @uses   \Brera\Http\HttpRequestBody
 * @uses   \Brera\Context\VersionedContext
 * @uses   \Brera\Context\ContextDecorator
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

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpsUrl::fromString('http://example.com/'),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->frontendFactory = new FrontendFactory($request);
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

    public function testItReturnsAContext()
    {
        $this->assertInstanceOf(Context::class, $this->frontendFactory->getContext());
    }

    public function testProductSearchResultsRouterIsReturned()
    {
        $result = $this->frontendFactory->createProductSearchResultsRouter();
        $this->assertInstanceOf(ProductSearchResultsRouter::class, $result);
    }
}
