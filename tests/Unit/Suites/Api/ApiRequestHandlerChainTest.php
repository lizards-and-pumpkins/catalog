<?php

namespace Brera\Api;

use Brera\Http\HttpRequest;

/**
 * @covers Brera\Api\ApiRequestHandlerChain
 */
class ApiRequestHandlerChainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApiRequestHandlerChain
     */
    private $requestHandlerChain;

    protected function setUp()
    {
        $this->requestHandlerChain = new ApiRequestHandlerChain();
    }

    public function testExceptionIsThrownDuringAttemptToRegisterRequestHandlerWithNonIntVersion()
    {
        $this->setExpectedException(ApiVersionMustBeIntException::class);

        $requestHandlerCode = 'foo';
        $requestHandlerMethod = HttpRequest::METHOD_GET;
        $requestHandlerVersion = 'bar';

        $stubApiRequestHandler = $this->getMock(ApiRequestHandler::class);
        $this->requestHandlerChain->register(
            $requestHandlerCode,
            $requestHandlerMethod,
            $requestHandlerVersion,
            $stubApiRequestHandler
        );
    }

    public function testExceptionIsThrownDuringAttemptToLocateRequestHandlerWithNonIntVersion()
    {
        $this->setExpectedException(ApiVersionMustBeIntException::class);

        $requestHandlerCode = 'foo';
        $requestHandlerMethod = HttpRequest::METHOD_GET;
        $requestHandlerVersion = 'bar';

        $this->requestHandlerChain->getApiRequestHandler(
            $requestHandlerCode,
            $requestHandlerMethod,
            $requestHandlerVersion
        );
    }

    public function testNullApiRequestHandlerIsReturnedIfNoApiRequestHandlerIsFound()
    {
        $requestHandlerCode = 'foo';
        $requestHandlerMethod = HttpRequest::METHOD_GET;
        $requestHandlerVersion = 1;

        $result = $this->requestHandlerChain->getApiRequestHandler(
            $requestHandlerCode,
            $requestHandlerMethod,
            $requestHandlerVersion
        );

        $this->assertInstanceOf(NullApiRequestHandler::class, $result);
    }

    public function testRequestHandlerIsReturned()
    {
        $requestHandlerCode = 'foo';
        $requestHandlerMethod = HttpRequest::METHOD_GET;
        $requestHandlerVersion = 1;

        $stubApiRequestHandler = $this->getMock(ApiRequestHandler::class);
        $this->requestHandlerChain->register(
            $requestHandlerCode,
            $requestHandlerMethod,
            $requestHandlerVersion,
            $stubApiRequestHandler
        );

        $result = $this->requestHandlerChain->getApiRequestHandler(
            $requestHandlerCode,
            $requestHandlerMethod,
            $requestHandlerVersion
        );

        $this->assertSame($stubApiRequestHandler, $result);
    }
}
