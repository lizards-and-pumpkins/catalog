<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import\RestApi;

use LizardsAndPumpkins\Import\ImportCatalogCommand;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Import\RestApi\CatalogImportApiV1PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\RestApi\CatalogImportApiV2PutRequestHandler
 * @uses   \LizardsAndPumpkins\Import\ImportCatalogCommand
 * @uses   \LizardsAndPumpkins\RestApi\ApiRequestHandler
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Http\ContentDelivery\GenericHttpResponse
 * @uses   \LizardsAndPumpkins\Http\HttpHeaders
 */
class CatalogImportApiV1PutRequestHandlerTest extends \PHPUnit\Framework\TestCase
{
    use TestFileFixtureTrait;

    public function testCreatesCommandsWithHardcodedDataVersion()
    {
        $testImportDirectoryPath = $this->getUniqueTempDir() . '/test/catalog-import-directory';
        $this->createFixtureDirectory($testImportDirectoryPath);

        $mockCommandQueue = $this->createMock(CommandQueue::class);

        $dummyLogger = $this->createMock(Logger::class);
        
        $stubRequest = $this->createMock(HttpRequest::class);

        $requestHandler = CatalogImportApiV1PutRequestHandler::create(
            $testImportDirectoryPath,
            $mockCommandQueue,
            $dummyLogger
        );
        
        $importFileName = 'import-file.xml';
        $this->createFixtureFile($testImportDirectoryPath . '/' . $importFileName, '');
        $stubRequest->method('getRawBody')->willReturn(json_encode(['fileName' => $importFileName]));

        $mockCommandQueue->expects($this->once())->method('add')
            ->willReturnCallback(function (ImportCatalogCommand $command) {
                $this->assertEquals('-1', $command->getDataVersion());
            });

        $response = $requestHandler->process($stubRequest);

        $this->assertSame(202, $response->getStatusCode());
        $this->assertSame('', $response->getBody());
    }
}
