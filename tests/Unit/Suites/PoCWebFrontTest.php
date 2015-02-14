<?php

namespace Brera;

use Brera\Environment\EnvironmentBuilder;
use Brera\Environment\Environment;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRouterChain;
use Brera\Http\HttpRouter;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpResponse;
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
 * @uses   \Brera\PoCUrlPathKeyGenerator
 * @uses   \Brera\UrlKeyRouter
 * @uses   \Brera\UrlKeyRequestHandler
 * @uses   \Brera\UrlKeyRequestHandlerBuilder
 * @uses   \Brera\Http\ResourceNotFoundRouter
 * @uses   \Brera\Http\ResourceNotFoundRequestHandler
 * @uses   \Brera\Http\HttpRouterChain
 * @uses   \Brera\Environment\EnvironmentDecorator
 * @uses   \Brera\Environment\WebsiteEnvironmentDecorator
 * @uses   \Brera\Environment\LanguageEnvironmentDecorator
 * @uses   \Brera\Environment\VersionedEnvironment
 * @uses   \Brera\Environment\EnvironmentBuilder
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
        $routerFactoryMethods = ['createApiRouter', 'createUrlKeyRouter', 'createResourceNotFoundRouter'];
        $stubFactoryMethods = array_merge(
            [ 'createEnvironmentBuilder', 'createHttpRouterChain', 'register'],
            $routerFactoryMethods
        );
        
        $stubMasterFactory = $this->getMock(MasterFactory::class, $stubFactoryMethods);
        $stubEnvironmentBuilder = $this->getMock(EnvironmentBuilder::class, [], [], '', false);
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
            ->method('createEnvironmentBuilder')
            ->willReturn($stubEnvironmentBuilder);
        $stubEnvironmentBuilder->expects($this->any())
            ->method('getEnvironment')
            ->willReturn($this->getMock(Environment::class));

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

    /**
     * @test
     */
    public function itShouldReturnMasterFactory()
    {
        $result = $this->webFront->getMasterFactory();
        $this->assertInstanceOf(MasterFactory::class, $result);
    }

    /**
     * @test
     */
    public function itShouldCallSendOnTheResponse()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $factory */
        $this->mockHttpResponse->expects($this->once())
            ->method('send');
        $this->webFront->run();
    }

    /**
     * @test
     */
    public function itShouldCreateAPoCMasterFactory()
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
