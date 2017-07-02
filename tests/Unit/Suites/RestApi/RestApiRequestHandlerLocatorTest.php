<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\Http\HttpRequest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\RestApi\RestApiRequestHandlerLocator
 */
class RestApiRequestHandlerLocatorTest extends TestCase
{
    /**
     * @var RestApiRequestHandlerLocator
     */
    private $requestHandlerLocator;

    /**
     * @var UrlToWebsiteMap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubUrlToWebsiteMap;

    /**
     * @param string $endpoint
     * @param string $requestMethod
     * @return HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubRequest(string $endpoint, string $requestMethod)
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->createMock(HttpRequest::class);
        $stubHttpRequest->method('hasHeader')->with('Accept')->willReturn(true);
        $stubHttpRequest->method('getHeader')->with('Accept')
            ->willReturn(sprintf('application/vnd.lizards-and-pumpkins.%s.v1+json', $endpoint));
        $stubHttpRequest->method('getMethod')->willReturn($requestMethod);

        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')
            ->willReturn(RestApiRequestHandlerLocator::API_URL_PREFIX . '/' . $endpoint);

        return $stubHttpRequest;
    }

    final protected function setUp()
    {
        $this->stubUrlToWebsiteMap = $this->createMock(UrlToWebsiteMap::class);
        $this->requestHandlerLocator = new RestApiRequestHandlerLocator($this->stubUrlToWebsiteMap);
    }

    public function testExceptionIsThrownDuringAttemptToRegisterRequestHandlerWithNonIntVersion()
    {
        $this->expectException(\TypeError::class);

        $requestHandlerCode = 'foo';
        $requestHandlerVersion = 'bar';

        $this->requestHandlerLocator->register($requestHandlerCode, $requestHandlerVersion, function () {
        });
    }

    public function testNullApiRequestHandlerIsReturnedIfNoApiRequestHandlerIsFound()
    {
        $endpoint = 'foo';
        $requestMethod = 'GET';
        $stubHttpRequest = $this->createStubRequest($endpoint, $requestMethod);

        $result = $this->requestHandlerLocator->getApiRequestHandler($stubHttpRequest);

        $this->assertInstanceOf(NullApiRequestHandler::class, $result);
    }

    public function testNullApiRequestHandlerIsReturnedIfRequestPathDoesNotContainRestApiPrefix()
    {
        $endpoint = 'foo';

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->createMock(HttpRequest::class);

        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn($endpoint);

        $result = $this->requestHandlerLocator->getApiRequestHandler($stubHttpRequest);

        $this->assertInstanceOf(NullApiRequestHandler::class, $result);
    }

    public function testNullApiRequestHandlerIsReturnedIfRequestDoesNotContainAcceptHeader()
    {
        $endpoint = 'foo';

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->createMock(HttpRequest::class);
        $stubHttpRequest->method('hasHeader')->with('Accept')->willReturn(false);

        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')
            ->willReturn(RestApiRequestHandlerLocator::API_URL_PREFIX . '/' . $endpoint);

        $result = $this->requestHandlerLocator->getApiRequestHandler($stubHttpRequest);

        $this->assertInstanceOf(NullApiRequestHandler::class, $result);
    }

    public function testNullApiRequestHandlerIsReturnedIfAcceptHeaderIsInvalid()
    {
        $endpoint = 'foo';

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->createMock(HttpRequest::class);
        $stubHttpRequest->method('hasHeader')->with('Accept')->willReturn(true);
        $stubHttpRequest->method('getHeader')->with('Accept')->willReturn('*');

        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')
            ->willReturn(RestApiRequestHandlerLocator::API_URL_PREFIX . '/' . $endpoint);

        $result = $this->requestHandlerLocator->getApiRequestHandler($stubHttpRequest);

        $this->assertInstanceOf(NullApiRequestHandler::class, $result);
    }

    public function testNullApiRequestHandlerIsReturnedIfRequestPathHasNoEndpointSpecified()
    {
        $endpoint = 'foo';

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubHttpRequest */
        $stubHttpRequest = $this->createMock(HttpRequest::class);
        $stubHttpRequest->method('hasHeader')->with('Accept')->willReturn(true);
        $stubHttpRequest->method('getHeader')->with('Accept')
            ->willReturn(sprintf('application/vnd.lizards-and-pumpkins.%s.v1+json', $endpoint));

        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')
            ->willReturn(RestApiRequestHandlerLocator::API_URL_PREFIX);

        $result = $this->requestHandlerLocator->getApiRequestHandler($stubHttpRequest);

        $this->assertInstanceOf(NullApiRequestHandler::class, $result);
    }

    public function testRequestHandlerIsReturned()
    {
        $endpoint = 'foo';
        $requestMethod = 'GET';
        $requestHandlerVersion = 1;

        $stubHttpRequest = $this->createStubRequest($endpoint, $requestMethod);

        $requestHandlerCode = sprintf('%s_%s', strtolower($requestMethod), $endpoint);
        $dummyHttpRequestHandler = $this->createMock(RestApiRequestHandler::class);
        $apiRequestHandlerFactory = function () use ($dummyHttpRequestHandler) {
            return $dummyHttpRequestHandler;
        };

        $this->requestHandlerLocator->register($requestHandlerCode, $requestHandlerVersion, $apiRequestHandlerFactory);

        $result = $this->requestHandlerLocator->getApiRequestHandler($stubHttpRequest);

        $this->assertSame($dummyHttpRequestHandler, $result);
    }
}
