<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\RestApi;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\RestApi\NullApiRequestHandler
 * @uses   \LizardsAndPumpkins\Http\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 */
class NullApiRequestHandlerTest extends TestCase
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

    public function testIsHttpRequestHandler()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    public function testRequestCanNotBeProcessed()
    {
        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testExceptionIsThrownDuringAttemptToProcess()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('NullApiRequestHandler should never be processed.');

        $this->requestHandler->process($this->stubRequest);
    }
}
