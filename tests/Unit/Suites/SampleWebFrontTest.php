<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpResponse;
use Brera\Http\HttpRouter;
use Brera\Http\HttpRouterChain;
use Brera\Http\HttpUrl;

/**
 * @covers \Brera\SampleWebFront
 * @covers \Brera\WebFront
 * @uses   \Brera\FactoryTrait
 * @uses   \Brera\MasterFactoryTrait
 * @uses   \Brera\FrontendFactory
 * @uses   \Brera\CommonFactory
 * @uses   \Brera\Content\ContentBlocksApiV1PutRequestHandler
 * @uses   \Brera\IntegrationTestFactory
 * @uses   \Brera\DataVersion
 * @uses   \Brera\PageBuilder
 * @uses   \Brera\SnippetKeyGeneratorLocator
 * @uses   \Brera\GenericSnippetKeyGenerator
 * @uses   \Brera\TemplatesApiV1PutRequestHandler
 * @uses   \Brera\Http\GenericHttpRouter
 * @uses   \Brera\Product\CatalogImportApiV1PutRequestHandler
 * @uses   \Brera\Product\ProductDetailViewRequestHandler
 * @uses   \Brera\Product\ProductListingRequestHandler
 * @uses   \Brera\Product\MultipleProductStockQuantityApiV1PutRequestHandler
 * @uses   \Brera\Product\ProductSearchRequestHandler
 * @uses   \Brera\RootSnippetSourceListBuilder
 * @uses   \Brera\Http\ResourceNotFoundRouter
 * @uses   \Brera\Http\ResourceNotFoundRequestHandler
 * @uses   \Brera\Http\HttpRouterChain
 * @uses   \Brera\Context\ContextDecorator
 * @uses   \Brera\Context\WebsiteContextDecorator
 * @uses   \Brera\Context\LocaleContextDecorator
 * @uses   \Brera\Context\VersionedContext
 * @uses   \Brera\Context\ContextBuilder
 * @uses   \Brera\Api\ApiRouter
 * @uses   \Brera\Api\ApiRequestHandlerChain
 * @uses   \Brera\DataPool\DataPoolReader
 * @uses   \Brera\Utils\Directory
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
            'createProductSearchResultsRouter'
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
        $mockUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $mockUrl->method('getPathRelativeToWebFront')->willReturn('foo');

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubHttpRequest->method('getUrl')->willReturn($mockUrl);

        $webFront = new SampleWebFront($stubHttpRequest);
        $webFront->registerFactory(new IntegrationTestFactory());
        $webFront->runWithoutSendingResponse();

        $this->assertInstanceOf(SampleMasterFactory::class, $webFront->getMasterFactory());
    }
}
