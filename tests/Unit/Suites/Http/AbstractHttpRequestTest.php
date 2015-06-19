<?php

namespace Brera\Http;

abstract class AbstractHttpRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testUrlIsReturned()
    {
        $url = 'http://www.example.com/seo-url/';

        $stubHttpUrl = $this->getStubHttpUrl();
        $stubHttpUrl->expects($this->once())
            ->method('__toString')
            ->willReturn($url);

        $httpRequest = new HttpPostRequest($stubHttpUrl);
        $result = $httpRequest->getUrl();

        $this->assertEquals($result, $url);
    }

    public function testUnsupportedRequestMethodExceptionIsThrown()
    {
        $this->setExpectedException(UnsupportedRequestMethodException::class, 'Unsupported request method: "PUT"');
        $stubHttpUrl = $this->getStubHttpUrl();
        HttpRequest::fromParameters('PUT', $stubHttpUrl);
    }

    public function testHttpIsRequestReturnedFromGlobalState()
    {
        $this->setUpGlobalState();

        $result = HttpRequest::fromGlobalState();

        $this->assertInstanceOf(HttpGetRequest::class, $result);
    }

    public function testHttpRequestIsReturnedFromGlobalStateOfSecureUrl()
    {
        $this->setUpGlobalState(true);

        $result = HttpRequest::fromGlobalState();

        $this->assertInstanceOf(HttpGetRequest::class, $result);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|HttpUrl
     */
    protected function getStubHttpUrl()
    {
        return $this->getMock(HttpUrl::class, [], [], '', false);
    }

    /**
     * @param bool $isSecure
     */
    private function setUpGlobalState($isSecure = false)
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTPS'] = $isSecure;
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['REQUEST_URI'] = '/';
    }
}
