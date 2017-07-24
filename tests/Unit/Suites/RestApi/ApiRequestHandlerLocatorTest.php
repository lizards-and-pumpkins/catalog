<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator
 */
class ApiRequestHandlerLocatorTest extends TestCase
{
    /**
     * @var ApiRequestHandlerLocator
     */
    private $requestHandlerChain;

    protected function setUp()
    {
        $this->requestHandlerChain = new ApiRequestHandlerLocator();
    }

    public function testExceptionIsThrownDuringAttemptToRegisterRequestHandlerWithNonIntVersion()
    {
        $this->expectException(\TypeError::class);

        $requestHandlerCode = 'foo';
        $requestHandlerVersion = 'bar';

        $this->requestHandlerChain->register($requestHandlerCode, $requestHandlerVersion, function () {
            return $this->createMock(ApiRequestHandler::class);
        });
    }

    public function testExceptionIsThrownDuringAttemptToLocateRequestHandlerWithNonIntVersion()
    {
        $this->expectException(\TypeError::class);

        $requestHandlerCode = 'foo';
        $requestHandlerVersion = 'bar';

        $this->requestHandlerChain->getApiRequestHandler($requestHandlerCode, $requestHandlerVersion);
    }

    public function testNullApiRequestHandlerIsReturnedIfNoApiRequestHandlerIsFound()
    {
        $requestHandlerCode = 'foo';
        $requestHandlerVersion = 1;

        $result = $this->requestHandlerChain->getApiRequestHandler($requestHandlerCode, $requestHandlerVersion);

        $this->assertInstanceOf(NullApiRequestHandler::class, $result);
    }

    public function testRequestHandlerIsReturned()
    {
        $requestHandlerCode = 'foo';
        $requestHandlerVersion = 1;

        $dummyHttpRequestHandler = $this->createMock(HttpRequestHandler::class);
        $apiRequestHandlerFactory = function () use ($dummyHttpRequestHandler) {
            return $dummyHttpRequestHandler;
        };
        $this->requestHandlerChain->register($requestHandlerCode, $requestHandlerVersion, $apiRequestHandlerFactory);

        $result = $this->requestHandlerChain->getApiRequestHandler($requestHandlerCode, $requestHandlerVersion);

        $this->assertSame($dummyHttpRequestHandler, $result);
    }
}
