<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RestApi;

use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Import\CatalogImport;
use LizardsAndPumpkins\Import\RestApi\Exception\CatalogImportProductDataNotFoundInRequestBodyException;
use LizardsAndPumpkins\Import\RestApi\Exception\DataVersionNotFoundInRequestBodyException;
use LizardsAndPumpkins\Import\XmlParser\ProductJsonToXml;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\RestApi\ProductImportApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 */
class ProductImportApiV1PutRequestHandlerTest extends TestCase
{
    private $productJson = 'DATA';

    /**
     * @var ProductImportApiV1PutRequestHandler
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
     * @var string
     */
    private $dummyDataVersion;

    /**
     * @return HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createValidRequestMock(): HttpRequest
    {
        $productJson = json_encode(['product_data' => $this->productJson, 'data_version' => $this->dummyDataVersion]);
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(HttpRequest::class);
        $request->method('getRawBody')->willReturn($productJson);

        return $request;
    }

    protected function setUp()
    {
        $this->mockProductJsonToXml = $this->createMock(ProductJsonToXml::class);
        $this->mockCatalogImport = $this->createMock(CatalogImport::class);
        $this->dummyDataVersion = '1.0.0';
        $this->handler = new ProductImportApiV1PutRequestHandler(
            $this->mockProductJsonToXml,
            $this->mockCatalogImport
        );
    }

    public function testIsProductImportRequestHandler()
    {
        $this->assertInstanceOf(ProductImportApiV1PutRequestHandler::class, $this->handler);
    }

    public function testImplementsApiRequestHandler()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->handler);
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

        $request = $this->createValidRequestMock();
        $response = $this->handler->process($request);

        $this->assertEquals($expectedBody, $response->getBody());
        $this->assertEquals($expectedCode, $response->getStatusCode());
    }

    public function testReturnsBadRequestResponseIfProductDataNotSet()
    {
        $this->expectException(CatalogImportProductDataNotFoundInRequestBodyException::class);
        $this->expectExceptionMessage('Product data not found in import product API request.');

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(HttpRequest::class);

        $this->handler->process($request);
    }

    public function testReturnsBadRequestResponseIfDataVersionNotSet()
    {
        $this->expectException(DataVersionNotFoundInRequestBodyException::class);
        $this->expectExceptionMessage('The catalog import data version is not found in request body.');

        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $request */
        $productWithoutDataVersionJson = json_encode(['product_data' => $this->productJson]);
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(HttpRequest::class);
        $request->method('getRawBody')->willReturn($productWithoutDataVersionJson);

        $this->handler->process($request);
    }

    public function testDelegatesToJsonToXmlInstance()
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
            ->with($productXml, $this->dummyDataVersion);

        $this->handler->process($request);
    }
}
