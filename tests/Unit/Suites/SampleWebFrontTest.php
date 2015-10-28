<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\HttpRouter;
use LizardsAndPumpkins\Http\HttpRouterChain;

/**
 * @covers \LizardsAndPumpkins\SampleWebFront
 * @covers \LizardsAndPumpkins\WebFront
 * @uses   \LizardsAndPumpkins\FactoryTrait
 * @uses   \LizardsAndPumpkins\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\FrontendFactory
 * @uses   \LizardsAndPumpkins\CommonFactory
 * @uses   \LizardsAndPumpkins\Content\ContentBlocksApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductDetailViewRequestHandler
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingRequestHandlerTrait
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingRequestHandler
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductSearchAutosuggestionRequestHandler
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\ProductSearchRequestHandler
 * @uses   \LizardsAndPumpkins\ContentDelivery\SnippetTransformation\PricesJsonSnippetTransformation
 * @uses   \LizardsAndPumpkins\IntegrationTestFactory
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\InMemorySearchEngine
 * @uses   \LizardsAndPumpkins\DataVersion
 * @uses   \LizardsAndPumpkins\PageBuilder
 * @uses   \LizardsAndPumpkins\SnippetKeyGeneratorLocator
 * @uses   \LizardsAndPumpkins\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\TemplatesApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\CatalogImport
 * @uses   \LizardsAndPumpkins\Http\GenericHttpRouter
 * @uses   \LizardsAndPumpkins\Product\CatalogImportApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Product\MultipleProductStockQuantityApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Product\ProductsPerPageForContextListBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductXmlToProductBuilderLocator
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ConfigurableProductXmlToProductBuilder
 * @uses   \LizardsAndPumpkins\Http\ResourceNotFoundRouter
 * @uses   \LizardsAndPumpkins\Http\ResourceNotFoundRequestHandler
 * @uses   \LizardsAndPumpkins\Http\HttpRouterChain
 * @uses   \LizardsAndPumpkins\Context\ContextDecorator
 * @uses   \LizardsAndPumpkins\Context\WebsiteContextDecorator
 * @uses   \LizardsAndPumpkins\Context\LocaleContextDecorator
 * @uses   \LizardsAndPumpkins\Context\VersionedContext
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder
 * @uses   \LizardsAndPumpkins\Context\ContextSource
 * @uses   \LizardsAndPumpkins\Api\ApiRouter
 * @uses   \LizardsAndPumpkins\Api\ApiRequestHandlerChain
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\Renderer\BlockRenderer
 * @uses   \LizardsAndPumpkins\Renderer\Translation\TranslatorRegistry
 * @uses   \LizardsAndPumpkins\Utils\Directory
 */
class SampleWebFrontTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SampleWebFront
     */
    private $webFront;

    /**
     * @var HttpResponse|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockHttpResponse;

    protected function setUp()
    {
        $routerFactoryMethods = [
            'createApiRouter',
            'createProductDetailViewRouter',
            'createProductListingRouter',
            'createResourceNotFoundRouter',
            'createProductSearchResultRouter',
            'createProductSearchAutosuggestionRouter',
        ];

        $stubFactoryMethods = array_merge(
            ['getContext', 'createHttpRouterChain', 'register'],
            $routerFactoryMethods
        );

        /** @var MasterFactory|\PHPUnit_Framework_MockObject_MockObject $stubMasterFactory */
        $stubMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods($stubFactoryMethods)
            ->getMock();

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $mockRouterChain = $this->getMock(HttpRouterChain::class);
        $mockHttpRequestHandler = $this->getMock(HttpRequestHandler::class);
        $this->mockHttpResponse = $this->getMock(HttpResponse::class);

        array_map(function ($methodName) use ($stubMasterFactory) {
            $stubMasterFactory->method($methodName)
                ->willReturn($this->getMock(HttpRouter::class));
        }, $routerFactoryMethods);

        $stubMasterFactory->method('getContext')->willReturn($this->getMock(Context::class));

        $stubMasterFactory->method('createHttpRouterChain')->willReturn($mockRouterChain);
        $mockRouterChain->method('route')->willReturn($mockHttpRequestHandler);
        $mockHttpRequestHandler->method('process')->willReturn($this->mockHttpResponse);

        $this->webFront = new TestSampleWebFront($stubHttpRequest, $stubMasterFactory);
    }

    public function testMasterFactoryIsReturned()
    {
        $result = $this->webFront->getMasterFactory();
        $this->assertInstanceOf(MasterFactory::class, $result);
    }

    public function testSendMethodOfResponseIsCalled()
    {
        $this->mockHttpResponse->expects($this->once())->method('send');
        $this->webFront->run();
    }

    public function testSampleMasterFactoryIsReturned()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubHttpRequest->method('getUrlPathRelativeToWebFront')->willReturn('foo');

        $webFront = new SampleWebFront($stubHttpRequest);
        $webFront->registerFactory(new IntegrationTestFactory());
        $webFront->runWithoutSendingResponse();

        $this->assertInstanceOf(SampleMasterFactory::class, $webFront->getMasterFactory());
    }
}
