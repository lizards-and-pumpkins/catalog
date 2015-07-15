<?php

namespace Brera\Product;

use Brera\Api\ApiRequestHandler;
use Brera\Http\HttpRequest;
use Brera\Queue\Queue;
use Brera\TestFileFixtureTrait;
use Brera\Utils\Directory;

/**
 * @covers \Brera\Product\MultipleProductStockQuantityApiRequestHandler
 * @uses   \Brera\Api\ApiRequestHandler
 * @uses   \Brera\DefaultHttpResponse
 * @uses   \Brera\Http\HttpHeaders
 * @uses   \Brera\Product\UpdateMultipleProductStockQuantityCommand
 */
class MultipleProductStockQuantityApiRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

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
     * @var MultipleProductStockQuantityApiRequestHandler
     */
    private $apiRequestHandler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequest;

    protected function setUp()
    {
        $this->importDirectoryPath = $this->getUniqueTempDir();
        $this->createFixtureDirectory($this->importDirectoryPath);

        $this->mockCommandQueue = $this->getMock(Queue::class);

        $this->mockDirectory = $this->getMock(Directory::class, [], [], '', false);
        $this->mockDirectory->method('isReadable')->willReturn(true);
        $this->mockDirectory->method('getPath')->willReturn($this->importDirectoryPath);

        $this->apiRequestHandler = MultipleProductStockQuantityApiRequestHandler::create(
            $this->mockCommandQueue,
            $this->mockDirectory
        );

        $this->mockRequest = $this->getMock(HttpRequest::class, [], [], '', false);
    }

    public function testClassIsDerivedFromApiRequestHandler()
    {
        $this->assertInstanceOf(ApiRequestHandler::class, $this->apiRequestHandler);
    }

    public function testCanProcessMethodAlwaysReturnsTrue()
    {
        $this->assertTrue($this->apiRequestHandler->canProcess());
    }

    public function testExceptionIsThrownIfImportDirectoryIsNotReadable()
    {
        $mockDirectory = $this->getMock(Directory::class, [], [], '', false);
        $mockDirectory->method('isReadable')->willReturn(false);

        $this->setExpectedException(CatalogImportDirectoryNotReadableException::class);

        MultipleProductStockQuantityApiRequestHandler::create($this->mockCommandQueue, $mockDirectory);
    }

    public function testExceptionIsThrownIfCatalogImportFileNameIsNotFoundInRequestBody()
    {
        $this->setExpectedException(CatalogImportFileNameNotFoundInRequestBodyException::class);
        $this->apiRequestHandler->process($this->mockRequest);
    }

    public function testExceptionIsThrownIfCatalogImportFileIsNotReadable()
    {
        $this->setExpectedException(CatalogImportFileNotReadableException::class);
        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['fileName' => 'foo']));
        $this->apiRequestHandler->process($this->mockRequest);
    }

    public function testUpdateMultipleProductStockQuantityCommandIsEmitted()
    {
        $fileName = 'foo';
        $this->createFixtureFile($this->importDirectoryPath . '/' . $fileName, '');
        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['fileName' => $fileName]));

        $this->mockCommandQueue->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(UpdateMultipleProductStockQuantityCommand::class));

        $response = $this->apiRequestHandler->process($this->mockRequest);

        $result = json_decode($response->getBody());
        $expectedJson = 'OK';

        $this->assertEquals($expectedJson, $result);
    }
}
