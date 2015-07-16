<?php

namespace Brera\Product;

use Brera\Http\HttpRequest;
use Brera\Queue\Queue;
use Brera\TestFileFixtureTrait;

/**
 * @covers \Brera\Product\CatalogImportApiRequestHandler
 * @uses   \Brera\Api\ApiRequestHandler
 * @uses   \Brera\DefaultHttpResponse
 * @uses   \Brera\Http\HttpHeaders
 * @uses   \Brera\Product\CatalogImportDomainEvent
 */
class CatalogImportApiRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var CatalogImportApiRequestHandler
     */
    private $apiRequestHandler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequest;

    /**
     * @var string
     */
    private $importDirectoryPath;

    protected function setUp()
    {
        $this->importDirectoryPath = $this->getUniqueTempDir();
        $this->createFixtureDirectory($this->importDirectoryPath);

        $this->mockDomainEventQueue = $this->getMock(Queue::class);
        $this->apiRequestHandler = CatalogImportApiRequestHandler::create(
            $this->mockDomainEventQueue,
            $this->importDirectoryPath
        );

        $this->mockRequest = $this->getMock(HttpRequest::class, [], [], '', false);
    }

    public function testCanProcessMethodAlwaysReturnsTrue()
    {
        $this->assertTrue($this->apiRequestHandler->canProcess($this->mockRequest));
    }

    public function testExceptionIsThrownIfImportDirectoryIsNotReadable()
    {
        $this->setExpectedException(CatalogImportDirectoryNotReadableException::class);
        CatalogImportApiRequestHandler::create($this->mockDomainEventQueue, '/some-not-existing-directory');
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

    public function testCatalogImportDomainEventIsEmitted()
    {
        $fileName = 'foo';

        $this->createFixtureFile($this->importDirectoryPath . '/' . $fileName, '');

        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['fileName' => $fileName]));

        $this->mockDomainEventQueue->expects($this->once())
            ->method('add')
            ->with($this->isInstanceOf(CatalogImportDomainEvent::class));

        $response = $this->apiRequestHandler->process($this->mockRequest);

        $result = json_decode($response->getBody());
        $expectedJson = 'OK';

        $this->assertEquals($expectedJson, $result);
    }
}
