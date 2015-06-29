<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Context\ContextBuilder;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpResponse;
use Brera\Http\HttpRouter;
use Brera\Http\HttpRouterChain;
use Brera\Http\HttpUrl;

/**
 * @covers \Brera\PoCWebFront
 * @covers \Brera\WebFront
 * @uses   \Brera\FactoryTrait
 * @uses   \Brera\MasterFactoryTrait
 * @uses   \Brera\FrontendFactory
 * @uses   \Brera\CommonFactory
 * @uses   \Brera\IntegrationTestFactory
 * @uses   \Brera\DataVersion
 * @uses   \Brera\PageBuilder
 * @uses   \Brera\PoCUrlPathKeyGenerator
 * @uses   \Brera\SnippetKeyGeneratorLocator
 * @uses   \Brera\GenericSnippetKeyGenerator
 * @uses   \Brera\Product\ProductDetailViewRouter
 * @uses   \Brera\Product\ProductDetailViewRequestHandler
 * @uses   \Brera\Product\ProductDetailViewRequestHandlerBuilder
 * @uses   \Brera\Product\ProductListingRouter
 * @uses   \Brera\Product\ProductListingRequestHandler
 * @uses   \Brera\Product\ProductListingRequestHandlerBuilder
 * @uses   \Brera\Product\ProductSnippetKeyGenerator
 * @uses   \Brera\Http\ResourceNotFoundRouter
 * @uses   \Brera\Http\ResourceNotFoundRequestHandler
 * @uses   \Brera\Http\HttpRouterChain
 * @uses   \Brera\Context\ContextDecorator
 * @uses   \Brera\Context\WebsiteContextDecorator
 * @uses   \Brera\Context\LanguageContextDecorator
 * @uses   \Brera\Context\VersionedContext
 * @uses   \Brera\Context\ContextBuilder
 * @uses   \Brera\Api\ApiRouter
 * @uses   \Brera\Api\ApiRequestHandlerChain
 * @uses   \Brera\DataPool\DataPoolReader
 */
class PoCWebFrontTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PoCWebFront
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
            'createResourceNotFoundRouter'
        ];

        $stubFactoryMethods = array_merge(
            ['createContextBuilder', 'createHttpRouterChain', 'register'],
            $routerFactoryMethods
        );

        $stubMasterFactory = $this->getMockBuilder(MasterFactory::class)
            ->setMethods($stubFactoryMethods)
            ->getMock();
        $stubContextBuilder = $this->getMock(ContextBuilder::class, [], [], '', false);

        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $mockRouterChain = $this->getMock(HttpRouterChain::class);
        $mockHttpRequestHandler = $this->getMock(HttpRequestHandler::class);
        $this->mockHttpResponse = $this->getMock(HttpResponse::class);

        array_map(function ($methodName) use ($stubMasterFactory) {
            $stubMasterFactory->expects($this->any())
                ->method($methodName)
                ->willReturn($this->getMock(HttpRouter::class));
        }, $routerFactoryMethods);

        $stubMasterFactory->expects($this->any())
            ->method('createContextBuilder')
            ->willReturn($stubContextBuilder);
        $stubContextBuilder->expects($this->any())
            ->method('getContext')
            ->willReturn($this->getMock(Context::class));
        $stubContextBuilder->expects($this->any())
            ->method('createFromRequest')
            ->willReturn($this->getMock(Context::class));

        $stubMasterFactory->expects($this->any())
            ->method('createHttpRouterChain')
            ->willReturn($mockRouterChain);
        $mockRouterChain->expects($this->any())
            ->method('route')
            ->willReturn($mockHttpRequestHandler);
        $mockHttpRequestHandler->expects($this->any())
            ->method('process')
            ->willReturn($this->mockHttpResponse);

        $this->webFront = new PoCWebFront($stubHttpRequest, $stubMasterFactory);
    }

    public function testMasterFactoryIsReturned()
    {
        $result = $this->webFront->getMasterFactory();
        $this->assertInstanceOf(MasterFactory::class, $result);
    }

    public function testSendMethodOfResponseIsCalled()
    {
        $this->mockHttpResponse->expects($this->once())
            ->method('send');
        $this->webFront->run();
    }

    public function testPoCMasterFactoryIsReturned()
    {
        $stubHttpRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubHttpRequest->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->getMock(HttpUrl::class, [], [], '', false));
        $webFront = new PoCWebFront($stubHttpRequest);
        $webFront->registerFactory(new IntegrationTestFactory());
        $webFront->runWithoutSendingResponse();
        $this->assertInstanceOf(PoCMasterFactory::class, $webFront->getMasterFactory());
    }
}
