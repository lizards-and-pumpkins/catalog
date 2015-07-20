<?php

namespace Brera\Product;

use Brera\Api\ApiRequestHandler;
use Brera\Http\HttpRequest;
use Brera\Image\ImageWasUpdatedDomainEvent;
use Brera\Queue\Queue;
use Brera\TestFileFixtureTrait;

/**
 * @covers \Brera\Product\CatalogImportApiV1PutRequestHandler
 * @uses   \Brera\Api\ApiRequestHandler
 * @uses   \Brera\DefaultHttpResponse
 * @uses   \Brera\Http\HttpHeaders
 * @uses   \Brera\Image\ImageWasUpdatedDomainEvent
 * @uses   \Brera\Product\ProductWasUpdatedDomainEvent
 * @uses   \Brera\Product\ProductListingWasUpdatedDomainEvent
 * @uses   \Brera\Utils\XPathParser
 */
class CatalogImportApiV1PutRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var Queue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

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
     * @var \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     */
    private $eventSpy;

    protected function setUp()
    {
        $this->importDirectoryPath = $this->getUniqueTempDir();
        $this->createFixtureDirectory($this->importDirectoryPath);

        $this->eventSpy = $this->any();

        $this->mockDomainEventQueue = $this->getMock(Queue::class);
        $this->mockDomainEventQueue->expects($this->eventSpy)->method('add');

        $stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $stubProductSource = $this->getMock(ProductSource::class, [], [], '', false);
        $stubProductSource->method('getId')->willReturn($stubProductId);

        $this->stubProductSourceBuilder = $this->getMock(ProductSourceBuilder::class, [], [], '', false);
        $this->stubProductSourceBuilder->method('createProductSourceFromXml')->willReturn($stubProductSource);

        $this->requestHandler = CatalogImportApiV1PutRequestHandler::create(
            $this->mockDomainEventQueue,
            $this->importDirectoryPath,
            $this->stubProductSourceBuilder
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
            $this->mockDomainEventQueue,
            '/some-not-existing-directory',
            $this->stubProductSourceBuilder
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

    public function testProductWasUpdatedDomainEventsAreEmitted()
    {
        $fileName = 'foo';
        $fileContents = file_get_contents(__DIR__ . '/../../../shared-fixture/catalog.xml');
        $this->createFixtureFile($this->importDirectoryPath . '/' . $fileName, $fileContents);

        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['fileName' => $fileName]));

        $response = $this->requestHandler->process($this->mockRequest);

        $result = json_decode($response->getBody());
        $expectedJson = 'OK';

        $this->assertEquals($expectedJson, $result);
        $this->assertEventWasAddedToAQueue(ProductWasUpdatedDomainEvent::class);
    }

    public function testProductListingWasUpdatedDomainEventsAreEmitted()
    {
        $fileName = 'foo';
        $fileContents = file_get_contents(__DIR__ . '/../../../shared-fixture/catalog.xml');
        $this->createFixtureFile($this->importDirectoryPath . '/' . $fileName, $fileContents);

        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['fileName' => $fileName]));

        $response = $this->requestHandler->process($this->mockRequest);

        $result = json_decode($response->getBody());
        $expectedJson = 'OK';

        $this->assertEquals($expectedJson, $result);
        $this->assertEventWasAddedToAQueue(ProductListingWasUpdatedDomainEvent::class);
    }

    public function testImageWasUpdatedDomainEventsAreEmitted()
    {
        $fileName = 'foo';
        $fileContents = file_get_contents(__DIR__ . '/../../../shared-fixture/catalog.xml');
        $this->createFixtureFile($this->importDirectoryPath . '/' . $fileName, $fileContents);

        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['fileName' => $fileName]));

        $response = $this->requestHandler->process($this->mockRequest);

        $result = json_decode($response->getBody());
        $expectedJson = 'OK';

        $this->assertEquals($expectedJson, $result);
        $this->assertEventWasAddedToAQueue(ImageWasUpdatedDomainEvent::class);
    }

    /**
     * @param string $eventClass
     */
    private function assertEventWasAddedToAQueue($eventClass)
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
