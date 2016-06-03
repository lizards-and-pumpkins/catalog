<?php

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\RestApi\Exception\ApiVersionMustBeIntException;

/**
 * @covers LizardsAndPumpkins\RestApi\ApiRequestHandlerLocator
 */
class ApiRequestHandlerLocatorTest extends \PHPUnit_Framework_TestCase
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
        $this->expectException(ApiVersionMustBeIntException::class);

        $requestHandlerCode = 'foo';
        $requestHandlerVersion = 'bar';

        /** @var ApiRequestHandler|\PHPUnit_Framework_MockObject_MockObject $stubApiRequestHandler */
        $stubApiRequestHandler = $this->createMock(ApiRequestHandler::class);
        $this->requestHandlerChain->register($requestHandlerCode, $requestHandlerVersion, $stubApiRequestHandler);
    }

    public function testExceptionIsThrownDuringAttemptToLocateRequestHandlerWithNonIntVersion()
    {
        $this->expectException(ApiVersionMustBeIntException::class);

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

        /** @var ApiRequestHandler|\PHPUnit_Framework_MockObject_MockObject $stubApiRequestHandler */
        $stubApiRequestHandler = $this->createMock(ApiRequestHandler::class);
        $this->requestHandlerChain->register($requestHandlerCode, $requestHandlerVersion, $stubApiRequestHandler);

        $result = $this->requestHandlerChain->getApiRequestHandler($requestHandlerCode, $requestHandlerVersion);

        $this->assertSame($stubApiRequestHandler, $result);
    }
}
