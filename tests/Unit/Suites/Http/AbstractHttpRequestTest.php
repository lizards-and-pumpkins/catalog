<?php

namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Http\Exception\CookieNotSetException;

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
        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->getMock(HttpUrl::class, [], [], '', false);

        $httpRequest = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );
        $result = $httpRequest->getUrl();

        $this->assertSame($stubHttpUrl, $result);
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
    
    public function testNullIsReturnedIfParameterIsAbsentInRequestQuery()
    {
        $result = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://example.com'),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->assertNull($result->getQueryParameter('foo'));
    }

    public function testQueryParameterRetrievalIsDelegatedToHttpUrl()
    {
        $queryParameterName = 'foo';
        $queryParameterValue = 'bar';

        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $stubHttpUrl->method('getQueryParameter')->with($queryParameterName)->willReturn($queryParameterValue);

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $this->assertEquals($queryParameterValue, $request->getQueryParameter($queryParameterName));
    }

    public function testQueryParametersExceptGivenRetrievalIsDelegatedToHttpUrl()
    {
        $queryParameterToBeExcluded = 'baz';
        $queryParametersWithParameterExcluded = ['foo' => 'bar'];

        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $stubHttpUrl->method('getQueryParametersExceptGiven')->with($queryParameterToBeExcluded)
            ->willReturn($queryParametersWithParameterExcluded);

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );
        $result = $request->getQueryParametersExceptGiven($queryParameterToBeExcluded);

        $this->assertSame($queryParametersWithParameterExcluded, $result);
    }

    public function testArrayOfCookiesIsReturned()
    {
        $expectedCookies = ['foo' => 'bar', 'baz' => 'qux'];

        $originalState = $_COOKIE;
        $_COOKIE = $expectedCookies;

        $request = HttpRequest::fromGlobalState();
        $result = $request->getCookies();

        $_COOKIE = $originalState;

        $this->assertSame($expectedCookies, $result);
    }

    public function testFalseIsReturnedIfRequestedCookieIsNotSet()
    {
        $request = HttpRequest::fromGlobalState();
        $this->assertFalse($request->hasCookie('foo'));
    }

    public function testTrueIsReturnedIfRequestedCookieIsSet()
    {
        $expectedCookieKey = 'foo';

        $originalState = $_COOKIE;
        $_COOKIE[$expectedCookieKey] = 'whatever';

        $request = HttpRequest::fromGlobalState();
        $result = $request->hasCookie($expectedCookieKey);

        $_COOKIE = $originalState;

        $this->assertTrue($result);
    }

    public function testExceptionIsThrownDuringAttemptToGetValueOfCookieWhichIsNotSet()
    {
        $request = HttpRequest::fromGlobalState();
        $this->setExpectedException(CookieNotSetException::class);
        $request->getCookieValue('foo');
    }

    public function testCookieValueIsReturned()
    {
        $expectedCookieName = 'foo';
        $expectedCookieValue = 'bar';

        $originalState = $_COOKIE;
        $_COOKIE = [$expectedCookieName => $expectedCookieValue];

        $request = HttpRequest::fromGlobalState();
        $result = $request->getCookieValue($expectedCookieName);

        $_COOKIE = $originalState;

        $this->assertSame($expectedCookieValue, $result);

    }
}
