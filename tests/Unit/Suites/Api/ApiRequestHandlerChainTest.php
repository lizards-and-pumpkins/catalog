<?php

namespace LizardsAndPumpkins\Api;

use LizardsAndPumpkins\Api\Exception\ApiVersionMustBeIntException;

/**
 * @covers LizardsAndPumpkins\Api\ApiRequestHandlerChain
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
        $requestHandlerVersion = 'bar';

        /** @var ApiRequestHandler|\PHPUnit_Framework_MockObject_MockObject $stubApiRequestHandler */
        $stubApiRequestHandler = $this->getMock(ApiRequestHandler::class);
        $this->requestHandlerChain->register($requestHandlerCode, $requestHandlerVersion, $stubApiRequestHandler);
    }

    public function testExceptionIsThrownDuringAttemptToLocateRequestHandlerWithNonIntVersion()
    {
        $this->setExpectedException(ApiVersionMustBeIntException::class);

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
        $stubApiRequestHandler = $this->getMock(ApiRequestHandler::class);
        $this->requestHandlerChain->register($requestHandlerCode, $requestHandlerVersion, $stubApiRequestHandler);

        $result = $this->requestHandlerChain->getApiRequestHandler($requestHandlerCode, $requestHandlerVersion);

        $this->assertSame($stubApiRequestHandler, $result);
    }
}
