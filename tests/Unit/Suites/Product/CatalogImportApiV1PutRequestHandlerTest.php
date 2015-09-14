<?php

namespace Brera\Product;

use Brera\Api\ApiRequestHandler;
use Brera\Http\HttpRequest;
use Brera\Log\Logger;
use Brera\Product\Exception\CatalogImportApiDirectoryNotReadableException;
use Brera\Product\Exception\CatalogImportFileNameNotFoundInRequestBodyException;
use Brera\Projection\Catalog\Import\CatalogImport;
use org\bovigo\vfs\vfsStream;

/**
 * @covers \Brera\Product\CatalogImportApiV1PutRequestHandler
 * @uses   \Brera\Api\ApiRequestHandler
 * @uses   \Brera\Http\HttpHeaders
 * @uses   \Brera\Http\HttpResponse
 * @uses   \Brera\DefaultHttpResponse
 */
class CatalogImportApiV1PutRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
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
        vfsStream::setup('root');
        $this->testImportDirectoryPath = vfsStream::url('root/catalog-import-directory');
        mkdir($this->testImportDirectoryPath, 0700, true);

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
        $this->setExpectedException(CatalogImportApiDirectoryNotReadableException::class);
        CatalogImportApiV1PutRequestHandler::create(
            $this->mockCatalogImport,
            '/some-not-existing-directory',
            $this->logger
        );
    }

    public function testExceptionIsThrownIfCatalogImportFileNameIsNotFoundInRequestBody()
    {
        $this->setExpectedException(CatalogImportFileNameNotFoundInRequestBodyException::class);
        $this->requestHandler->process($this->mockRequest);
    }

    public function testItDelegatesTheCatalogImport()
    {
        $importFileName = 'import-file.xml';
        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['fileName' => $importFileName]));

        $this->mockCatalogImport->expects($this->once())->method('importFile')
            ->with($this->stringEndsWith('/' . $importFileName));

        $this->requestHandler->process($this->mockRequest);
    }
}
