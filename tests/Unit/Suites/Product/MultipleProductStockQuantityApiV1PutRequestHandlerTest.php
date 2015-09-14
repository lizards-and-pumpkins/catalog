<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Api\ApiRequestHandler;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Product\Exception\CatalogImportApiDirectoryNotReadableException;
use LizardsAndPumpkins\Product\Exception\CatalogImportFileNameNotFoundInRequestBodyException;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\Utils\Directory;

/**
 * @covers \LizardsAndPumpkins\Product\MultipleProductStockQuantityApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Api\ApiRequestHandler
 * @uses   \LizardsAndPumpkins\DefaultHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Product\UpdateMultipleProductStockQuantityCommand
 * @uses   \LizardsAndPumpkins\Utils\XPathParser
 */
class MultipleProductStockQuantityApiV1PutRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommandQueue;

    /**
     * @var Directory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDirectory;

    /**
     * @var string
     */
    private $importDirectoryPath;

    /**
     * @var ProductStockQuantitySourceBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductStockQuantitySourceBuilder;

    /**
     * @var MultipleProductStockQuantityApiV1PutRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequest;

    protected function setUp()
    {
        $this->importDirectoryPath = __DIR__ . '/../../../shared-fixture';

        $this->mockCommandQueue = $this->getMock(Queue::class);

        $this->mockDirectory = $this->getMock(Directory::class, [], [], '', false);
        $this->mockDirectory->method('isReadable')->willReturn(true);
        $this->mockDirectory->method('getPath')->willReturn($this->importDirectoryPath);

        $this->mockProductStockQuantitySourceBuilder = $this->getMock(
            ProductStockQuantitySourceBuilder::class,
            [],
            [],
            '',
            false
        );

        $this->requestHandler = MultipleProductStockQuantityApiV1PutRequestHandler::create(
            $this->mockCommandQueue,
            $this->mockDirectory,
            $this->mockProductStockQuantitySourceBuilder
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
        $mockDirectory = $this->getMock(Directory::class, [], [], '', false);
        $mockDirectory->method('isReadable')->willReturn(false);

        $this->setExpectedException(CatalogImportApiDirectoryNotReadableException::class);

        MultipleProductStockQuantityApiV1PutRequestHandler::create(
            $this->mockCommandQueue,
            $mockDirectory,
            $this->mockProductStockQuantitySourceBuilder
        );
    }

    public function testExceptionIsThrownIfCatalogImportFileNameIsNotFoundInRequestBody()
    {
        $this->setExpectedException(CatalogImportFileNameNotFoundInRequestBodyException::class);
        $this->requestHandler->process($this->mockRequest);
    }

    public function testExceptionIsThrownIfCatalogImportFileIsNotReadable()
    {
        $this->setExpectedException(CatalogImportFileNotReadableException::class);
        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['fileName' => 'foo']));
        $this->requestHandler->process($this->mockRequest);
    }

    public function testUpdateMultipleProductStockQuantityCommandIsEmitted()
    {
        $fileName = 'stock.xml';
        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['fileName' => $fileName]));

        $this->mockCommandQueue->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(UpdateMultipleProductStockQuantityCommand::class));

        $response = $this->requestHandler->process($this->mockRequest);

        $result = json_decode($response->getBody());
        $expectedJson = 'OK';

        $this->assertEquals($expectedJson, $result);
    }
}
