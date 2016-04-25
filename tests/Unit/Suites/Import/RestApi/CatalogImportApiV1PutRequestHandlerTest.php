<?php

namespace LizardsAndPumpkins\Import\RestApi;

use LizardsAndPumpkins\RestApi\ApiRequestHandler;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Import\RestApi\Exception\CatalogImportApiDirectoryNotReadableException;
use LizardsAndPumpkins\Import\RestApi\Exception\CatalogImportFileNameNotFoundInRequestBodyException;
use LizardsAndPumpkins\Import\CatalogImport;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Import\RestApi\CatalogImportApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandler
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpResponse
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 */
class CatalogImportApiV1PutRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;
    
    /**
     * @var CatalogImportApiV1PutRequestHandler
     */
    private $requestHandler;

    /**
     * @var string
     */
    private $testImportDirectoryPath;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequest;

    /**
     * @var CatalogImport|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCatalogImport;

    protected function setUp()
    {
        $this->testImportDirectoryPath = $this->getUniqueTempDir() . '/test/catalog-import-directory';
        $this->createFixtureDirectory($this->testImportDirectoryPath);
        
        $this->mockCatalogImport = $this->getMock(CatalogImport::class, [], [], '', false);

        $this->logger = $this->getMock(Logger::class);

        $this->requestHandler = CatalogImportApiV1PutRequestHandler::create(
            $this->mockCatalogImport,
            $this->testImportDirectoryPath,
            $this->logger
        );

        $this->mockRequest = $this->getMock(HttpRequest::class, [], [], '', false);
    }

    public function testClassIsDerivedFromApiRequestHandler()
    {
        $this->assertInstanceOf(ApiRequestHandler::class, $this->requestHandler);
    }

    public function testRequestCanNotBeProcessedIfMethodIsNotPut()
    {
        $this->mockRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->assertFalse($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testRequestCanBeProcessedIfMethodIsPut()
    {
        $this->mockRequest->method('getMethod')->willReturn(HttpRequest::METHOD_PUT);
        $this->assertTrue($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testExceptionIsThrownIfImportDirectoryIsNotReadable()
    {
        $this->expectException(CatalogImportApiDirectoryNotReadableException::class);
        CatalogImportApiV1PutRequestHandler::create(
            $this->mockCatalogImport,
            '/some-not-existing-directory',
            $this->logger
        );
    }

    public function testExceptionIsThrownIfCatalogImportFileNameIsNotFoundInRequestBody()
    {
        $this->expectException(CatalogImportFileNameNotFoundInRequestBodyException::class);
        $this->requestHandler->process($this->mockRequest);
    }

    public function testItDelegatesTheCatalogImport()
    {
        $importFileName = 'import-file.xml';
        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['fileName' => $importFileName]));

        $this->mockCatalogImport->expects($this->once())->method('importFile')
            ->with($this->stringEndsWith('/' . $importFileName));

        $response = $this->requestHandler->process($this->mockRequest);
        
        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('', $response->getBody());
    }
}
