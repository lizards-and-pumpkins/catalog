<?php
declare(strict_types=1);

namespace Import\RestApi;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\RestApi\ProductImportApiV2PutRequestHandler;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;
use PHPUnit\Framework\TestCase;

class ProductImportApiV2PutRequestHandlerTest extends TestCase
{
    /**
     * @var ProductImportApiV2PutRequestHandler
     */
    private $handler;

    public function testIsProductImportRequestHandler()
    {
        $this->assertInstanceOf(ProductImportApiV2PutRequestHandler::class, $this->handler);
    }

    public function testImplementsApiRequestHandler()
    {
        $this->assertInstanceOf(ApiRequestHandler::class, $this->handler);
    }

    public function testCanProcessPut()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(HttpRequest::class);
        $request->method('getMethod')->willReturn(HttpRequest::METHOD_PUT);
        $this->assertTrue($this->handler->canProcess($request));
    }

    public function testCanNotProcessGet()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(HttpRequest::class);
        $request->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->assertFalse($this->handler->canProcess($request));
    }

    public function testResponse()
    {
        $expectedCode = HttpResponse::STATUS_ACCEPTED;
        $expectedBody = '';
        $expectedHeaders = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => '*',
            'Content-Type' => 'application/json',
        ];

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(HttpRequest::class);
        $response = $this->handler->process($request);

        $this->assertEquals($expectedCode, $response->getStatusCode());
        $this->assertEquals($expectedBody, $response->getBody());
        $this->assertEquals($expectedHeaders, $response->getHeaders()->getAll());
    }

    protected function setUp()
    {
        $this->handler = new ProductImportApiV2PutRequestHandler();
    }
}
