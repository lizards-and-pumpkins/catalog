<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import\RestApi;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\ImportCatalogCommand;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\TestFileFixtureTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\RestApi\CatalogImportApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\RestApi\CatalogImportApiV2PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\ImportCatalogCommand
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Http\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 */
class CatalogImportApiV1PutRequestHandlerTest extends TestCase
{
    use TestFileFixtureTrait;

    public function testCreatesCommandsWithCurrentDataVersion(): void
    {
        $testDataVersion = 'foo';
        
        $testImportDirectoryPath = $this->getUniqueTempDir() . '/test/catalog-import-directory';
        $this->createFixtureDirectory($testImportDirectoryPath);

        /** @var CommandQueue|MockObject $mockCommandQueue */
        $mockCommandQueue = $this->createMock(CommandQueue::class);

        /** @var Logger|MockObject $dummyLogger */
        $dummyLogger = $this->createMock(Logger::class);

        /** @var HttpRequest|MockObject $stubRequest */
        $stubRequest = $this->createMock(HttpRequest::class);

        /** @var DataVersion|MockObject $stubDataVersion */
        $stubDataVersion = $this->createMock(DataVersion::class);
        $stubDataVersion->method('__toString')->willReturn($testDataVersion);

        $requestHandler = new CatalogImportApiV1PutRequestHandler(
            $testImportDirectoryPath,
            $mockCommandQueue,
            $dummyLogger,
            $stubDataVersion
        );
        
        $importFileName = 'import-file.xml';
        $this->createFixtureFile($testImportDirectoryPath . '/' . $importFileName, '');
        $stubRequest->method('getRawBody')->willReturn(json_encode(['fileName' => $importFileName]));

        $mockCommandQueue->expects($this->once())->method('add')
            ->willReturnCallback(function (ImportCatalogCommand $command) use ($testDataVersion) {
                $this->assertEquals($testDataVersion, $command->getDataVersion());
            });

        $response = $requestHandler->process($stubRequest);

        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('', $response->getBody());
    }
}
