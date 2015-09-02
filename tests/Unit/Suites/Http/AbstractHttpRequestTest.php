<?php

namespace Brera\Http;

abstract class AbstractHttpRequestTest extends \PHPUnit_Framework_TestCase
{
    private $testRequestHost = 'example.com';

    /**
     * @param bool $isSecure
     */
    private function setUpGlobalState($isSecure = false)
    {
        $_SERVER['REQUEST_METHOD'] = HttpRequest::METHOD_GET;
        $_SERVER['HTTPS'] = $isSecure;
        $_SERVER['HTTP_HOST'] = $this->testRequestHost;
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['QUERY_STRING'] = '';
    }

    public function testUrlIsReturned()
    {
        $url = 'http://www.example.com/seo-url/';

        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $stubHttpUrl->method('__toString')->willReturn($url);

        $httpRequest = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );
        $result = $httpRequest->getUrl();

        $this->assertEquals($url, $result);
    }

    public function testUrlPathRelativeToWebFrontIsReturned()
    {
        $path = 'foo';

        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $stubHttpUrl->method('getPathRelativeToWebFront')->willReturn($path);

        $httpRequest = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );
        $this->assertSame($path, $httpRequest->getUrlPathRelativeToWebFront());
    }

    public function testUnsupportedRequestMethodExceptionIsThrown()
    {
        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->getMock(HttpUrl::class, [], [], '', false);

        $this->setExpectedException(UnsupportedRequestMethodException::class, 'Unsupported request method: "XXX"');

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
    
    public function testEmptyStringIsReturnedIfParameterIsAbsentInRequestQuery()
    {
        $result = HttpRequest::fromGlobalState();
        $this->assertSame('', $result->getQueryParameter('foo'));
    }

    public function testQueryParameterIsReturned()
    {
        $queryParameterName = 'foo';
        $queryParameterValue = 'bar';

        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $stubHttpUrl->method('getQueryParameter')->with($queryParameterName)->willReturn($queryParameterValue);

        $result = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->assertEquals($queryParameterValue, $result->getQueryParameter($queryParameterName));
    }
}
