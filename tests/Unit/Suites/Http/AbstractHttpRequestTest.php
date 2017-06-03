<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Http\Exception\CookieNotSetException;
use LizardsAndPumpkins\Http\Exception\QueryParameterDoesNotExistException;
use PHPUnit\Framework\TestCase;

abstract class AbstractHttpRequestTest extends TestCase
{
    private $testRequestHost = 'example.com';

    /**
     * @var string[]
     */
    private $originalServerState;

    /**
     * @var string[]
     */
    private $originalCookieState;

    /**
     * @before
     */
    final public function saveGlobalsState()
    {
        $this->originalServerState = $_SERVER;
        $this->originalCookieState = $_COOKIE;
    }

    /**
     * @after
     */
    final public function restoreGlobalsState()
    {
        $_SERVER = $this->originalServerState;
        $_COOKIE = $this->originalCookieState;
    }

    private function setUpGlobalState(bool $isSecure = false)
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
        $stubHttpUrl = $this->createMock(HttpUrl::class);

        $httpRequest = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $result = $httpRequest->getUrl();

        $this->assertSame($stubHttpUrl, $result);
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

    public function testDelegatesCheckingOfQueryParameterExistenceToHttpUrl()
    {
        $queryParameterName = 'foo';

        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->createMock(HttpUrl::class);
        $stubHttpUrl->method('hasQueryParameter')->with($queryParameterName)->willReturn(false);

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );

        $this->assertFalse($request->hasQueryParameter($queryParameterName));
    }

    public function testThrowsAnExceptionDuringAttemptToRetrieveNonExistingQueryParameterValue()
    {
        $this->expectException(QueryParameterDoesNotExistException::class);

        $queryParameterName = 'foo';

        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->createMock(HttpUrl::class);
        $stubHttpUrl->method('hasQueryParameter')->with($queryParameterName)->willReturn(false);

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );

        $request->getQueryParameter($queryParameterName);
    }

    public function testQueryParameterRetrievalIsDelegatedToHttpUrl()
    {
        $queryParameterName = 'foo';
        $queryParameterValue = 'bar';

        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->createMock(HttpUrl::class);
        $stubHttpUrl->method('hasQueryParameter')->with($queryParameterName)->willReturn(true);
        $stubHttpUrl->method('getQueryParameter')->with($queryParameterName)->willReturn($queryParameterValue);

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );

        $this->assertEquals($queryParameterValue, $request->getQueryParameter($queryParameterName));
    }

    public function testDelegatesToUrlToCheckIfQueryParametersArePresent()
    {
        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->createMock(HttpUrl::class);
        $stubHttpUrl->expects($this->once())->method('hasQueryParameters')->willReturn(true);

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $this->assertTrue($request->hasQueryParameters());
    }

    public function testArrayOfCookiesIsReturned()
    {
        $this->setUpGlobalState();

        $expectedCookies = ['foo' => 'bar', 'baz' => 'qux'];
        $_COOKIE = $expectedCookies;

        $request = HttpRequest::fromGlobalState();
        $result = $request->getCookies();

        $this->assertSame($expectedCookies, $result);
    }

    public function testFalseIsReturnedIfRequestedCookieIsNotSet()
    {
        $this->setUpGlobalState();

        $request = HttpRequest::fromGlobalState();
        $this->assertFalse($request->hasCookie('foo'));
    }

    public function testTrueIsReturnedIfRequestedCookieIsSet()
    {
        $this->setUpGlobalState();

        $expectedCookieKey = 'foo';
        $_COOKIE[$expectedCookieKey] = 'whatever';

        $request = HttpRequest::fromGlobalState();
        $result = $request->hasCookie($expectedCookieKey);

        $this->assertTrue($result);
    }

    public function testExceptionIsThrownDuringAttemptToGetValueOfCookieWhichIsNotSet()
    {
        $this->setUpGlobalState();

        $request = HttpRequest::fromGlobalState();
        $this->expectException(CookieNotSetException::class);
        $request->getCookieValue('foo');
    }

    public function testCookieValueIsReturned()
    {
        $this->setUpGlobalState();

        $expectedCookieName = 'foo';
        $expectedCookieValue = 'bar';
        $_COOKIE = [$expectedCookieName => $expectedCookieValue];

        $request = HttpRequest::fromGlobalState();
        $result = $request->getCookieValue($expectedCookieName);

        $this->assertSame($expectedCookieValue, $result);
    }

    public function testItDelegatesToTheHttpUrlToRetrieveTheRequestHost()
    {
        /** @var HttpUrl|\PHPUnit_Framework_MockObject_MockObject $stubHttpUrl */
        $stubHttpUrl = $this->createMock(HttpUrl::class);
        $stubHttpUrl->method('getHost')->willReturn('example.com');

        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );
        $this->assertSame('example.com', $request->getHost());
    }

    public function testDelegatesCheckingIfHeaderExistsToHttpHeaders()
    {
        $headerName = 'foo';

        $stubHttpUrl = $this->createMock(HttpUrl::class);

        $stubHttpHeaders = $this->createMock(HttpHeaders::class);
        $stubHttpHeaders->method('has')->with($headerName)->willReturn(true);

        $httpRequest = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            $stubHttpUrl,
            $stubHttpHeaders,
            new HttpRequestBody('')
        );

        $this->assertTrue($httpRequest->hasHeader($headerName));
    }
}
