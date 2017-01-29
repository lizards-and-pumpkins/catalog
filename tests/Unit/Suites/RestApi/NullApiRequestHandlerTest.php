<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Http\HttpRequest;

/**
 * @covers \LizardsAndPumpkins\RestApi\NullApiRequestHandler
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandler
 */
class NullApiRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NullApiRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    protected function setUp()
    {
        $this->requestHandler = new NullApiRequestHandler;
        $this->stubRequest = $this->createMock(HttpRequest::class);
    }

    public function testApiRequestHandlerIsExtended()
    {
        $this->assertInstanceOf(ApiRequestHandler::class, $this->requestHandler);
    }

    public function testRequestCanNotBeProcessed()
    {
        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testExceptionIsThrownDuringAttemptToProcess()
    {
        $this->requestHandler->process($this->stubRequest);

        $response = $this->requestHandler->process($this->stubRequest);
        $expectedResponseBody = json_encode(['error' => 'NullApiRequestHandler should never be processed.']);

        $this->assertSame($expectedResponseBody, $response->getBody());
    }
}
