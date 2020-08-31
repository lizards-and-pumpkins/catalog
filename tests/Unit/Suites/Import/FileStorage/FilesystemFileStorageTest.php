<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\FileStorage;

use LizardsAndPumpkins\Import\FileStorage\Exception\FileDoesNotExistException;
use LizardsAndPumpkins\Import\FileStorage\Exception\FileStorageTypeMismatchException;
use LizardsAndPumpkins\Util\FileSystem\TestFileFixtureTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\FileStorage\FilesystemFileStorage
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FilesystemFileUri
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FileInStorage
 * @uses   \LizardsAndPumpkins\Import\FileStorage\StorageAgnosticFileUri
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FileContent
 */
class FilesystemFileStorageTest extends TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var FilesystemFileStorage
     */
    private $fileStorage;

    /**
     * @var string
     */
    private $testBaseDirectory;

    /**
     * @var string
     */
    private $testFileContent = '*** test content ***';

    /**
     * @var File
     */
    private $mockFile;

    final protected function setUp(): void
    {
        $this->testBaseDirectory = $this->getUniqueTempDir();
        $this->mockFile = $this->createMock(File::class);
        $this->fileStorage = new FilesystemFileStorage($this->testBaseDirectory);
    }

    public function testItImplementsTheFileStorageInterface(): void
    {
        $this->assertInstanceOf(FileStorage::class, $this->fileStorage);
    }

    public function testItReturnsAFileInstance(): void
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/readme.md');

        $file = $this->fileStorage->getFileReference($fileURI);
        $this->assertInstanceOf(File::class, $file);
    }

    public function testContainsReturnsTrueIfTheFileExists(): void
    {
        $fileURI = 'test/readme.md';
        $this->createFixtureFile($this->testBaseDirectory . '/' . $fileURI, $this->testFileContent);
        $this->assertTrue($this->fileStorage->contains(StorageAgnosticFileUri::fromString($fileURI)));
    }

    public function testContainsReturnsFalseIfTheFileNotExists(): void
    {
        $this->assertFalse($this->fileStorage->contains(StorageAgnosticFileUri::fromString('non-existing')));
    }

    public function testPutContentCreatesAFileIfItDoesNotExist(): void
    {
        $fileURI = 'this/is/a/new-file';
        $file = $this->testBaseDirectory . '/' . $fileURI;
        $this->addFileToCleanupAfterTest($file);

        $content = FileContent::fromString($this->testFileContent);
        $identifier = StorageAgnosticFileUri::fromString($fileURI);
        $this->fileStorage->putContent($identifier, $content);

        $this->assertFileExists($file);
        $this->assertSame($this->testFileContent, file_get_contents($file));
    }

    public function testPutContentUpdatesFileContentsIfTheFileDoesNotYetExist(): void
    {
        $fileURI = 'this/is/an/existing-file';
        $filesystemPath = $this->testBaseDirectory . '/' . $fileURI;
        $this->createFixtureFile($filesystemPath, 'some other content');

        $content = FileContent::fromString($this->testFileContent);
        $identifier = StorageAgnosticFileUri::fromString($fileURI);
        $this->fileStorage->putContent($identifier, $content);

        $this->assertFileExists($filesystemPath);
        $this->assertSame($this->testFileContent, file_get_contents($filesystemPath));
    }

    public function testGetContentThrowsAnExceptionIfTheFileDoesNotExist(): void
    {
        $fileURI = 'non-existing-file';
        $this->expectException(FileDoesNotExistException::class);
        $this->expectExceptionMessage('Unable to get contents of non-existing file "non-existing-file"');
        $identifier = StorageAgnosticFileUri::fromString($fileURI);
        $this->fileStorage->getContent($identifier);
    }

    public function testGetContentReturnsTheContentsOfAnExistingFile(): void
    {
        $fileURI = 'non-existing-file';
        $filesystemPath = $this->testBaseDirectory . '/' . $fileURI;
        $this->createFixtureFile($filesystemPath, $this->testFileContent);

        $identifier = StorageAgnosticFileUri::fromString($fileURI);
        $fileContent = $this->fileStorage->getContent($identifier);
        
        $this->assertInstanceOf(FileContent::class, $fileContent);
        $this->assertSame($this->testFileContent, (string) $fileContent);
    }

    public function testItImplementsTheFileToFileStorageInterfaces(): void
    {
        $this->assertInstanceOf(FileToFileStorage::class, $this->fileStorage);
    }

    /**
     * @dataProvider methodWithFileArgumentProvider
     * @param string $methodWithFileArgument
     */
    public function testItThrowsAnExceptionIfTheFileStorageTypeDoesNotMatch(string $methodWithFileArgument): void
    {
        $this->expectException(FileStorageTypeMismatchException::class);
        $this->expectExceptionMessage(
            sprintf('FileStorage %s not compatible with file OtherFileStorageUri', get_class($this->fileStorage))
        );
        $stubOtherStorageTypeURI = $this->getMockBuilder(StorageSpecificFileUri::class)
            ->setMockClassName('OtherFileStorageUri')
            ->getMock();
        $this->mockFile->method('getInStorageUri')->willReturn($stubOtherStorageTypeURI);

        call_user_func([$this->fileStorage, $methodWithFileArgument], $this->mockFile);
    }

    /**
     * @return array[]
     */
    public function methodWithFileArgumentProvider() : array
    {
        return [
            'isPresent' => ['isPresent'],
            'read'      => ['read'],
            'write'     => ['write'],
        ];
    }

    public function testIsPresentReturnsFalseForANotExistingFile(): void
    {
        $filesystemPath = $this->testBaseDirectory . '/non-existing';
        $this->mockFile->method('getInStorageUri')->willReturn(FilesystemFileUri::fromString('/non-existing'));
        $this->mockFile->method('__toString')->willReturn($filesystemPath);

        $this->assertFalse($this->fileStorage->isPresent($this->mockFile));
    }

    public function testItReturnsTrueForAnExistingFile(): void
    {
        $this->mockFile->method('getInStorageUri')->willReturn(FilesystemFileUri::fromString('/existing-file'));
        $filesystemPath = $this->testBaseDirectory . '/existing-file';
        $this->mockFile->method('__toString')->willReturn($filesystemPath);
        $this->createFixtureFile($filesystemPath, $this->testFileContent);

        $this->assertTrue($this->fileStorage->isPresent($this->mockFile));
    }

    public function testWriteCreatesAFileIfItDoesNotExist(): void
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/file-to-create');
        $filesystemPath = $this->testBaseDirectory . '/' . $fileURI;
        $this->addFileToCleanupAfterTest($filesystemPath);

        $this->mockFile->method('getInStorageUri')->willReturn(FilesystemFileUri::fromString($filesystemPath));
        $this->mockFile->method('__toString')->willReturn($filesystemPath);
        $this->mockFile->method('getContent')->willReturn(FileContent::fromString($this->testFileContent));

        $this->fileStorage->write($this->mockFile);

        $this->assertFileExists($filesystemPath);
        $this->assertSame($this->testFileContent, file_get_contents($filesystemPath));
    }

    public function testWriteUpdateAnExistingFile(): void
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/file-to-update');
        $filesystemPath = $this->testBaseDirectory . '/' . $fileURI;
        $this->createFixtureFile($filesystemPath, 'some other content');

        $this->mockFile->method('getInStorageUri')->willReturn(FilesystemFileUri::fromString($filesystemPath));
        $this->mockFile->method('__toString')->willReturn($filesystemPath);
        $this->mockFile->method('getContent')->willReturn(FileContent::fromString($this->testFileContent));

        $this->fileStorage->write($this->mockFile);

        $this->assertSame($this->testFileContent, file_get_contents($filesystemPath));
    }

    public function testReadReturnsAnExistingFilesContent(): void
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/file-to-read');
        $filesystemPath = $this->testBaseDirectory . '/' . $fileURI;
        $this->mockFile->method('getInStorageUri')->willReturn(FilesystemFileUri::fromString($filesystemPath));
        $this->mockFile->method('__toString')->willReturn($filesystemPath);
        $this->createFixtureFile($filesystemPath, $this->testFileContent);

        $content = $this->fileStorage->read($this->mockFile);
        
        $this->assertIsString($content);
        $this->assertSame($this->testFileContent, $content);
    }

    public function testReadThrowsAnExceptionForNonExistentFiles(): void
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/file-to-read');
        $filesystemPath = $this->testBaseDirectory . '/' . $fileURI;
        $this->expectException(FileDoesNotExistException::class);
        $this->expectExceptionMessage(sprintf('Unable to get contents of non-existing file "%s"', $filesystemPath));
        $this->mockFile->method('getInStorageUri')->willReturn(FilesystemFileUri::fromString($filesystemPath));
        $this->mockFile->method('__toString')->willReturn($filesystemPath);

        $this->fileStorage->read($this->mockFile);
    }
}
