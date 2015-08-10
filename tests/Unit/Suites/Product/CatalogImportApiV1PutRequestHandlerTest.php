<?php

namespace Brera\Product;

use Brera\Api\ApiRequestHandler;
use Brera\Http\HttpRequest;
use Brera\Image\UpdateImageCommand;
use Brera\Logger;
use Brera\Queue\Queue;
use Brera\TestFileFixtureTrait;

/**
 * @covers \Brera\Product\CatalogImportApiV1PutRequestHandler
 * @uses   \Brera\Api\ApiRequestHandler
 * @uses   \Brera\DefaultHttpResponse
 * @uses   \Brera\Http\HttpHeaders
 * @uses   \Brera\Image\ImageWasUpdatedDomainEvent
 * @uses   \Brera\Image\UpdateImageCommand
 * @uses   \Brera\Product\ProductWasUpdatedDomainEvent
 * @uses   \Brera\Product\ProductListingWasUpdatedDomainEvent
 * @uses   \Brera\Product\UpdateProductCommand
 * @uses   \Brera\Product\UpdateProductListingCommand
 * @uses   \Brera\Utils\XPathParser
 */
class CatalogImportApiV1PutRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockCommandQueue;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var CatalogImportApiV1PutRequestHandler
     */
    private $requestHandler;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRequest;

    /**
     * @var string
     */
    private $importDirectoryPath;

    /**
     * @var ProductSourceBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductSourceBuilder;

    /**
     * @var ProductListingSourceBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductListingSourceBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     */
    private $eventSpy;

    protected function setUp()
    {
        $this->importDirectoryPath = $this->getUniqueTempDir();
        $this->createFixtureDirectory($this->importDirectoryPath);

        $this->eventSpy = $this->any();

        $this->mockCommandQueue = $this->getMock(Queue::class);
        $this->mockCommandQueue->expects($this->eventSpy)->method('add');

        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $stubProductSource = $this->getMock(ProductSource::class, [], [], '', false);
        $stubProductSource->method('getId')->willReturn($stubProductId);

        $this->stubProductSourceBuilder = $this->getMock(ProductSourceBuilder::class, [], [], '', false);
        $this->stubProductSourceBuilder->method('createProductSourceFromXml')->willReturn($stubProductSource);

        $dummyUrlKey = 'foo';
        $stubProductListingSource = $this->getMock(ProductListingSource::class, [], [], '', false);
        $stubProductListingSource->method('getUrlKey')->willReturn($dummyUrlKey);

        $this->stubProductListingSourceBuilder = $this->getMock(ProductListingSourceBuilder::class, [], [], '', false);
        $this->stubProductListingSourceBuilder->method('createProductListingSourceFromXml')
            ->willReturn($stubProductListingSource);

        $this->logger = $this->getMock(Logger::class);

        $this->requestHandler = CatalogImportApiV1PutRequestHandler::create(
            $this->mockCommandQueue,
            $this->importDirectoryPath,
            $this->stubProductSourceBuilder,
            $this->stubProductListingSourceBuilder,
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
        $this->setExpectedException(CatalogImportDirectoryNotReadableException::class);
        CatalogImportApiV1PutRequestHandler::create(
            $this->mockCommandQueue,
            '/some-not-existing-directory',
            $this->stubProductSourceBuilder,
            $this->stubProductListingSourceBuilder,
            $this->logger
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

    public function testExceptionIsThrownIfProductSourceDataWasNotCreated()
    {
        $fileName = 'foo';
        $fileContents = file_get_contents(__DIR__ . '/../../../shared-fixture/catalog-with-invalid-product.xml');
        $this->createFixtureFile($this->importDirectoryPath . '/' . $fileName, $fileContents);

        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['fileName' => $fileName]));

        $exceptionMessage = 'bar';
        $this->stubProductSourceBuilder->method('createProductSourceFromXml')
            ->willThrowException(new AttributeContextPartsMismatchException($exceptionMessage));

        $this->logger->expects($this->atLeastOnce())->method('log')
            ->with($this->isInstanceOf(ProductImportFailedMessage::class));

        $this->requestHandler->process($this->mockRequest);
    }

    public function testUpdateProductCommandsAreEmitted()
    {
        $fileName = 'foo';
        $fileContents = file_get_contents(__DIR__ . '/../../../shared-fixture/catalog.xml');
        $this->createFixtureFile($this->importDirectoryPath . '/' . $fileName, $fileContents);

        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['fileName' => $fileName]));

        $response = $this->requestHandler->process($this->mockRequest);

        $result = json_decode($response->getBody());
        $expectedJson = 'OK';

        $this->assertEquals($expectedJson, $result);
        $this->assertCommandWasAddedToAQueue(UpdateProductCommand::class);
    }

    public function testUpdateProductListingCommandsAreEmitted()
    {
        $fileName = 'foo';
        $fileContents = file_get_contents(__DIR__ . '/../../../shared-fixture/catalog.xml');
        $this->createFixtureFile($this->importDirectoryPath . '/' . $fileName, $fileContents);

        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['fileName' => $fileName]));

        $response = $this->requestHandler->process($this->mockRequest);

        $result = json_decode($response->getBody());
        $expectedJson = 'OK';

        $this->assertEquals($expectedJson, $result);
        $this->assertCommandWasAddedToAQueue(UpdateProductListingCommand::class);
    }

    public function testUpdateImageCommandsAreEmitted()
    {
        $fileName = 'foo';
        $fileContents = file_get_contents(__DIR__ . '/../../../shared-fixture/catalog.xml');
        $this->createFixtureFile($this->importDirectoryPath . '/' . $fileName, $fileContents);

        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['fileName' => $fileName]));

        $response = $this->requestHandler->process($this->mockRequest);

        $result = json_decode($response->getBody());
        $expectedJson = 'OK';

        $this->assertEquals($expectedJson, $result);
        $this->assertCommandWasAddedToAQueue(UpdateImageCommand::class);
    }

    /**
     * @param string $eventClass
     */
    private function assertCommandWasAddedToAQueue($eventClass)
    {
        $numberOfRequiredInvocations = 0;

        /** @var \PHPUnit_Framework_MockObject_Invocation_Object $invocation */
        foreach ($this->eventSpy->getInvocations() as $invocation) {
            if ($eventClass === get_class($invocation->parameters[0])) {
                $numberOfRequiredInvocations++;
            }
        }

        $this->assertGreaterThan(
            0,
            $numberOfRequiredInvocations,
            sprintf('Failed to assert that %s was added to event queue.', $eventClass)
        );
    }
}
