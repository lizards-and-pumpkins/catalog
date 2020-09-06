<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RestApi;

use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Import\ImportCatalogCommand;
use LizardsAndPumpkins\Import\RestApi\Exception\CatalogImportApiDirectoryIsNotDirectoryException;
use LizardsAndPumpkins\Import\RestApi\Exception\CatalogImportFileNameNotFoundInRequestBodyException;
use LizardsAndPumpkins\Import\RestApi\Exception\DataVersionNotFoundInRequestBodyException;
use LizardsAndPumpkins\Import\RestApi\Exception\InvalidDataVersionTypeException;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Import\RestApi\Exception\CatalogImportApiDirectoryNotReadableException;
use LizardsAndPumpkins\Util\FileSystem\TestFileFixtureTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\RestApi\CatalogImportApiV2PutRequestHandler
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 * @uses   \LizardsAndPumpkins\Http\HttpResponse
 * @uses   \LizardsAndPumpkins\Http\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Import\ImportCatalogCommand
 */
class CatalogImportApiV2PutRequestHandlerTest extends TestCase
{
    use TestFileFixtureTrait;
    
    /**
     * @var CatalogImportApiV2PutRequestHandler
     */
    private $requestHandler;

    /**
     * @var string
     */
    private $testImportDirectoryPath;

    /**
     * @var Logger|MockObject
     */
    private $logger;

    /**
     * @var HttpRequest|MockObject
     */
    private $mockRequest;

    /**
     * @var CommandQueue|MockObject
     */
    private $mockCommandQueue;

    final protected function setUp(): void
    {
        $this->testImportDirectoryPath = $this->getUniqueTempDir() . '/test/catalog-import-directory';
        $this->createFixtureDirectory($this->testImportDirectoryPath);
        
        $this->mockCommandQueue = $this->createMock(CommandQueue::class);

        $this->logger = $this->createMock(Logger::class);

        $this->requestHandler = new CatalogImportApiV2PutRequestHandler(
            $this->testImportDirectoryPath,
            $this->mockCommandQueue,
            $this->logger
        );

        $this->mockRequest = $this->createMock(HttpRequest::class);
    }

    public function testIsHttpRequestHandler(): void
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    public function testRequestCanNotBeProcessedIfMethodIsNotPut(): void
    {
        $this->mockRequest->method('getMethod')->willReturn(HttpRequest::METHOD_GET);
        $this->assertFalse($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testRequestCanBeProcessedIfMethodIsPut(): void
    {
        $this->mockRequest->method('getMethod')->willReturn(HttpRequest::METHOD_PUT);
        $this->assertTrue($this->requestHandler->canProcess($this->mockRequest));
    }

    public function testExceptionIsThrownIfImportDirectoryIsNotReadable(): void
    {
        $this->expectException(CatalogImportApiDirectoryNotReadableException::class);
        new CatalogImportApiV2PutRequestHandler('/some-not-existing-directory', $this->mockCommandQueue, $this->logger);
    }

    public function testExceptionIsThrownIfImportDirectoryIsNotDirectory(): void
    {
        $this->expectException(CatalogImportApiDirectoryIsNotDirectoryException::class);
        new CatalogImportApiV2PutRequestHandler(__FILE__, $this->mockCommandQueue, $this->logger);
    }

    public function testExceptionIsThrownIfCatalogImportFileNameIsNotFoundInRequestBody(): void
    {
        $this->expectException(CatalogImportFileNameNotFoundInRequestBodyException::class);
        $this->expectExceptionMessage('Import file name is not found in request body.');

        $this->requestHandler->process($this->mockRequest);
    }

    public function testExceptionIsThrownIfDataVersionIsNotFoundInRequestBody(): void
    {
        $this->expectException(DataVersionNotFoundInRequestBodyException::class);
        $this->expectExceptionMessage('The catalog import data version is not found in request body.');

        $importFileName = 'import-file.xml';
        $this->createFixtureFile($this->testImportDirectoryPath . '/' . $importFileName, '');
        $this->mockRequest->method('getRawBody')->willReturn(json_encode(['fileName' => $importFileName]));

        $this->requestHandler->process($this->mockRequest);
    }

    public function testAddsImportCatalogCommandToCommandQueue(): void
    {
        $dataVersion = 'foo-bar';
        $importFileName = 'import-file.xml';

        $this->createFixtureFile($this->testImportDirectoryPath . '/' . $importFileName, '');

        $this->mockRequest->method('getRawBody')->willReturn(json_encode([
            'fileName' => $importFileName,
            'dataVersion' => $dataVersion
        ]));

        $this->mockCommandQueue->expects($this->once())->method('add')
            ->willReturnCallback(function (ImportCatalogCommand $command) use ($dataVersion) {
                $this->assertEquals($dataVersion, $command->getDataVersion());
            });

        $response = $this->requestHandler->process($this->mockRequest);
        
        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('', $response->getBody());
    }

    /**
     * @dataProvider nonCastableDataVersionProvider
     * @param mixed $dataVersion
     */
    public function testExceptionIsThrownIfRequestBodyContainsDataVersionOfInvalidType($dataVersion): void
    {
        $this->expectException(InvalidDataVersionTypeException::class);

        $importFileName = 'import-file.xml';

        $this->createFixtureFile($this->testImportDirectoryPath . '/' . $importFileName, '');

        $this->mockRequest->method('getRawBody')->willReturn(json_encode([
            'fileName' => $importFileName,
            'dataVersion' => $dataVersion
        ]));

        $this->requestHandler->process($this->mockRequest);
    }

    /**
     * @dataProvider castableDataVersionsProvider
     * @param int|float $dataVersion
     */
    public function testDataVersionIsCastedToString($dataVersion): void
    {
        $importFileName = 'import-file.xml';

        $this->createFixtureFile($this->testImportDirectoryPath . '/' . $importFileName, '');

        $this->mockRequest->method('getRawBody')->willReturn(json_encode([
            'fileName' => $importFileName,
            'dataVersion' => $dataVersion
        ]));

        $this->mockCommandQueue->expects($this->once())->method('add')
                               ->willReturnCallback(function (ImportCatalogCommand $command) use ($dataVersion) {
                                   $this->assertEquals((string) $dataVersion, $command->getDataVersion());
                               });

        $response = $this->requestHandler->process($this->mockRequest);

        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('', $response->getBody());
    }

    public function castableDataVersionsProvider(): array
    {
        return ['int' => [12], 'float' => [.2]];
    }

    public function nonCastableDataVersionProvider(): array
    {
        return ['boolean' => [true], 'array' => [['foo']]];
    }
}
