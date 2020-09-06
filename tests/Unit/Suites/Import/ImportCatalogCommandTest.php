<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Exception\CatalogImportFileDoesNotExistException;
use LizardsAndPumpkins\Import\Exception\CatalogImportFileIsNotAFileException;
use LizardsAndPumpkins\Import\Exception\CatalogImportFileNotReadableException;
use LizardsAndPumpkins\Import\Exception\NotImportCatalogCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\Util\FileSystem\TestFileFixtureTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ImportCatalogCommand
 * @uses \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class ImportCatalogCommandTest extends TestCase
{
    use TestFileFixtureTrait;
    
    private function createCommand(string $dataVersionString, string $catalogData): ImportCatalogCommand
    {
        return new ImportCatalogCommand(DataVersion::fromVersionString($dataVersionString), $catalogData);
    }

    public function testImplementsCommandInterface(): void
    {
        $this->assertInstanceOf(Command::class, $this->createCommand('123test', __FILE__));
    }

    public function testReturnsCatalogImportMessage(): void
    {
        $message = $this->createCommand('123test', __FILE__)->toMessage();

        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(ImportCatalogCommand::CODE, $message->getName());
        
        $this->assertArrayHasKey('data_version', $message->getMetadata());
        $this->assertSame('123test', $message->getMetadata()['data_version']);
        
        $this->assertArrayHasKey('catalog_data_file', $message->getPayload());
        $this->assertSame(__FILE__, $message->getPayload()['catalog_data_file']);
    }

    public function testCanBeRehydratedFromImportCatalogCommandMessage(): void
    {
        $sourceMessage = $this->createCommand('test123', __FILE__)->toMessage();
        $rehydratedCommand = ImportCatalogCommand::fromMessage($sourceMessage);
        
        $this->assertInstanceOf(ImportCatalogCommand::class, $rehydratedCommand);
        
        $this->assertSame($sourceMessage->getPayload()['catalog_data_file'], $rehydratedCommand->getCatalogDataFile());
        
        $this->assertSame('test123', (string) $rehydratedCommand->getDataVersion());
    }

    public function testThrowsExceptionIfMessageNameDoesNotMatch(): void
    {
        $this->expectException(NotImportCatalogCommandMessageException::class);
        $this->expectExceptionMessage('Unable to rehydrate from "foo" queue message, expected "import_catalog"');

        $message = Message::withCurrentTime('foo', [], []);

        ImportCatalogCommand::fromMessage($message);
    }

    public function testThrowsExceptionIfImportFileDoesNotExist(): void
    {
        $fakeFileName = '/foo/does/not/exist';
        $this->expectException(CatalogImportFileDoesNotExistException::class);
        $this->expectExceptionMessage("Catalog import file \"{$fakeFileName}\" does not exist");
        $this->createCommand('test-test', $fakeFileName);
    }

    public function testThrowsExceptionIfImportFileIsNotReadable(): void
    {
        $tmpFilePath = $this->getUniqueTempDir() . uniqid('test-');
        $this->createFixtureFile($tmpFilePath, '', 0000);
        $this->expectException(CatalogImportFileNotReadableException::class);
        $this->expectExceptionMessage("Catalog import file \"{$tmpFilePath}\" is not readable");
        $this->createCommand('another bogus name', $tmpFilePath);
    }

    public function testThrowsExceptionIfImportFileIsDirectory(): void
    {
        $tmpFilePath = $this->getUniqueTempDir();
        mkdir($tmpFilePath, 0777, true);
        $this->expectException(CatalogImportFileIsNotAFileException::class);
        $this->expectExceptionMessage("Catalog import file \"{$tmpFilePath}\" is not a file");
        $this->createCommand('another bogus name', $tmpFilePath);
    }
}
