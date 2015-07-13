<?php

namespace Brera\Http;

abstract class AbstractHttpRequestTest extends \PHPUnit_Framework_TestCase
{
    private $testRequestHost = 'example.com';

    /**
     * @return HttpUrl|\PHPUnit_Framework_MockObject_MockObject
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
        $_SERVER['REQUEST_METHOD'] = HttpRequest::HTTP_GET_REQUEST;
        $_SERVER['HTTPS'] = $isSecure;
        $_SERVER['HTTP_HOST'] = $this->testRequestHost;
        $_SERVER['REQUEST_URI'] = '/';
    }

    public function testUrlIsReturned()
    {
        $url = 'http://www.example.com/seo-url/';

        $stubHttpUrl = $this->getStubHttpUrl();
        $stubHttpUrl->expects($this->once())
            ->method('__toString')
            ->willReturn($url);

        $httpRequest = HttpRequest::fromParameters(
            HttpRequest::HTTP_GET_REQUEST,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );
        $result = $httpRequest->getUrl();

        $this->assertEquals($result, $url);
    }

    public function testUnsupportedRequestMethodExceptionIsThrown()
    {
        $this->setExpectedException(UnsupportedRequestMethodException::class, 'Unsupported request method: "XXX"');
        $stubHttpUrl = $this->getStubHttpUrl();
        HttpRequest::fromParameters('XXX', $stubHttpUrl, HttpHeaders::fromArray([]), HttpRequestBody::fromString(''));
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

    public function testItReturnsARequestHeader()
    {
        $this->setUpGlobalState();
        $result = HttpRequest::fromGlobalState();
        $this->assertSame($this->testRequestHost, $result->getHeader('host'));
    }

    public function testItDefaultsToAnEmptyRequestBody()
    {
        $this->setUpGlobalState();
        $result = HttpRequest::fromGlobalState();
        $this->assertSame('', $result->getRawBody());
    }

    public function testItReturnsAnInjectedRequestBody()
    {
        $testRequestBody = 'the request body';
        $this->setUpGlobalState();
        $result = HttpRequest::fromGlobalState($testRequestBody);
        $this->assertSame($testRequestBody, $result->getRawBody());
    }
}
