<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpRouter;
use Brera\Http\HttpUrl;

abstract class AbstractRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return HttpRouter
     */
    abstract protected function getRouterUnderTest();

    /**
     * @return HttpRequestHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    abstract protected function getMockRequestHandler();
    
    public function testHttpRouterInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRouter::class, $this->getRouterUnderTest());
    }

    public function testNullIsReturnedIfRequestHandlerIsUnableToProcessRequest()
    {
        $this->getMockRequestHandler()->expects($this->once())
            ->method('canProcess')
            ->willReturn(false);
        $stubRequest = $this->getStubRequest();
        $stubContext = $this->getMock(Context::class);
        $this->assertNull($this->getRouterUnderTest()->route($stubRequest, $stubContext));
    }

    public function testRequestHandlerIsReturnedIfRequestHandlerCanProcessRequest()
    {
        $this->getMockRequestHandler()->expects($this->once())
            ->method('canProcess')
            ->willReturn(true);
        $stubRequest = $this->getStubRequest();
        $stubContext = $this->getMock(Context::class);
        $this->assertSame($this->getMockRequestHandler(), $this->getRouterUnderTest()->route(
            $stubRequest,
            $stubContext
        ));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubRequest()
    {
        $stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubRequest->method('getUrl')
            ->willReturn(HttpUrl::fromString('http://example.com/'));

        return $stubRequest;
    }
}
