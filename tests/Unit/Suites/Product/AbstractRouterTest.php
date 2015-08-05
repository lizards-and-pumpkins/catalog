<?php

namespace Brera\Product;

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
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequest */
        $stubRequest = $this->getStubRequest();
        $this->getMockRequestHandler()->expects($this->once())->method('canProcess')->willReturn(false);

        $this->assertNull($this->getRouterUnderTest()->route($stubRequest));
    }

    public function testRequestHandlerIsReturnedIfRequestHandlerCanProcessRequest()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequest */
        $stubRequest = $this->getStubRequest();
        $this->getMockRequestHandler()->expects($this->once())->method('canProcess')->willReturn(true);
        $this->assertSame($this->getMockRequestHandler(), $this->getRouterUnderTest()->route($stubRequest));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubRequest()
    {
        $stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubRequest->method('getUrl')->willReturn(HttpUrl::fromString('http://example.com/'));

        return $stubRequest;
    }
}
