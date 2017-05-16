<?php

declare(strict_types=1);

namespace Import\RestApi;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\CatalogImport;
use LizardsAndPumpkins\Import\RestApi\ProductImportApiV2PutRequestHandler;
use LizardsAndPumpkins\Import\XmlParser\ProductJsonToXml;
use LizardsAndPumpkins\RestApi\ApiRequestHandler;
use PHPUnit\Framework\TestCase;

class ProductImportApiV2PutRequestHandlerTest extends TestCase
{
    private $productJson = 'DATA';

    /**
     * @var ProductImportApiV2PutRequestHandler
     */
    private $handler;

    /**
     * @var ProductJsonToXml|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductJsonToXml;

    /**
     * @var CatalogImport|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCatalogImport;

    /**
     * @var DataVersion
     */
    private $dataVersion;


    /**
     * @return HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createValidRequestMock(): HttpRequest
    {
        $productJson = json_encode(['product_data' => $this->productJson]);
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(HttpRequest::class);
        $request->method('getRawBody')->willReturn($productJson);
        return $request;
    }


    protected function setUp()
    {
        $this->mockProductJsonToXml = $this->createMock(ProductJsonToXml::class);
        $this->mockCatalogImport = $this->createMock(CatalogImport::class);
        $this->dataVersion = $this->createMock(DataVersion::class);
        $this->handler = new ProductImportApiV2PutRequestHandler(
            $this->mockProductJsonToXml,
            $this->mockCatalogImport,
            $this->dataVersion
        );
    }

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
        $request = $this->createValidRequestMock();
        $request->method('getMethod')->willReturn(HttpRequest::METHOD_PUT);
        $this->assertTrue($this->handler->canProcess($request));
    }

    public function testCanNotProcessGet()
    {
        $request = $this->createValidRequestMock();
        $request->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->assertFalse($this->handler->canProcess($request));
    }

    public function testReturnsAcceptResponseForValidRequest()
    {
        $expectedCode = HttpResponse::STATUS_ACCEPTED;
        $expectedBody = '';
        $expectedHeaders = [
            'Access-Control-Allow-Origin'  => '*',
            'Access-Control-Allow-Methods' => '*',
            'Content-Type'                 => 'application/json',
        ];

        $request = $this->createValidRequestMock();
        $response = $this->handler->process($request);

        $this->assertEquals($expectedBody, $response->getBody());
        $this->assertEquals($expectedCode, $response->getStatusCode());
        $this->assertEquals($expectedHeaders, $response->getHeaders()->getAll());
    }

    public function testReturnsBadRequestResponseIfProductDataNotSet()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(HttpRequest::class);

        $response = $this->handler->process($request);

        $this->assertEquals(HttpResponse::STATUS_BAD_REQUEST, $response->getStatusCode());
        $this->assertJson($response->getBody());
        $this->assertEquals(
            'Product data not found in import product API request.',
            json_decode($response->getBody(), true)['error']
        );
    }

    public function testSendJsonAndCallJsonToXmlWithIt()
    {
        $request = $this->createValidRequestMock();
        $this->mockProductJsonToXml->expects($this->once())->method('toXml')->with($this->productJson);

        $this->handler->process($request);
    }

    public function testCallsCatalogImportInstanceWithProductXml()
    {
        $request = $this->createValidRequestMock();
        $productXml = 'PRODUCT_XML';
        $this->mockProductJsonToXml->expects($this->once())->method('toXml')->willReturn($productXml);

        $this->mockCatalogImport
            ->expects($this->once())
            ->method('addProductsAndProductImagesToQueue')
            ->with($productXml, $this->dataVersion);

        $this->handler->process($request);
    }
}
