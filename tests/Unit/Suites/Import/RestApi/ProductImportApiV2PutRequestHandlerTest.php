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
    private $productJsonToXmlMock;
    /**
     * @var CatalogImport|\PHPUnit_Framework_MockObject_MockObject
     */
    private $catalogImport;
    /**
     * @var DataVersion
     */
    private $dataVersion;

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

    /**
     * @return HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createValidRequestMock(): HttpRequest
    {
        $productJson = json_encode(['productData' => $this->productJson]);
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(HttpRequest::class);
        $request->method('getRawBody')->willReturn($productJson);
        return $request;
    }

    public function testCanNotProcessGet()
    {
        $request = $this->createValidRequestMock();
        $request->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->assertFalse($this->handler->canProcess($request));
    }

    public function testResponse()
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

        $this->assertEquals($expectedCode, $response->getStatusCode());
        $this->assertEquals($expectedBody, $response->getBody());
        $this->assertEquals($expectedHeaders, $response->getHeaders()->getAll());
    }

    public function testReturnsBadRequestIfProductDataNotSet()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(HttpRequest::class);

        $response = $this->handler->process($request);

        $this->assertEquals(HttpResponse::STATUS_BAD_REQUEST, $response->getStatusCode());
        $this->assertJson($response->getBody());
        $this->assertEquals(
            'Product data is not found in request body.',
            json_decode($response->getBody(), true)['error']
        );
    }

    public function testSendJsonAndCallJsonToXmlWithIt()
    {
        $request = $this->createValidRequestMock();
        $this->productJsonToXmlMock->expects($this->once())->method('toXml')->with($this->productJson);

        $this->handler->process($request);
    }

    public function testSendXmlToCatalogImport()
    {
        $request = $this->createValidRequestMock();
        $productXml = 'PRODUCT_XML';
        $this->productJsonToXmlMock->expects($this->once())->method('toXml')->willReturn($productXml);

        $this->catalogImport
            ->expects($this->once())
            ->method('addProductsAndProductImagesToQueue')
            ->with($productXml, $this->dataVersion);

        $this->handler->process($request);
    }

    protected function setUp()
    {
        $this->productJsonToXmlMock = $this->createMock(ProductJsonToXml::class);
        $this->catalogImport = $this->createMock(CatalogImport::class);
        $this->dataVersion = $this->createMock(DataVersion::class);
        $this->handler = new ProductImportApiV2PutRequestHandler(
            $this->productJsonToXmlMock,
            $this->catalogImport,
            $this->dataVersion
        );
    }
}
