<?php

namespace LizardsAndPumpkins\Import\FileStorage;

use LizardsAndPumpkins\TestFileFixtureTrait;
use LizardsAndPumpkins\Import\FileStorage\Exception\FileDoesNotExistException;
use LizardsAndPumpkins\Import\FileStorage\Exception\FileStorageTypeMismatchException;

/**
 * @covers \LizardsAndPumpkins\Import\FileStorage\FilesystemFileStorage
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FilesystemFileUri
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FileInStorage
 * @uses   \LizardsAndPumpkins\Import\FileStorage\StorageAgnosticFileUri
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FileContent
 */
class FilesystemFileStorageTest extends \PHPUnit_Framework_TestCase
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
     * @var File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockFile;

    protected function setUp()
    {
        $this->testBaseDirectory = $this->getUniqueTempDir();
        $this->mockFile = $this->getMock(File::class);
        $this->fileStorage = new FilesystemFileStorage($this->testBaseDirectory);
    }

    public function testItImplementsTheFileStorageInterface()
    {
        $this->assertInstanceOf(FileStorage::class, $this->fileStorage);
    }

    public function testItReturnsAFileInstance()
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/readme.md');

        $file = $this->fileStorage->getFileReference($fileURI);
        $this->assertInstanceOf(File::class, $file);
    }

    public function testContainsReturnsTrueIfTheFileExists()
    {
        $fileURI = 'test/readme.md';
        $this->createFixtureFile($this->testBaseDirectory . '/' . $fileURI, $this->testFileContent);
        $this->assertTrue($this->fileStorage->contains(StorageAgnosticFileUri::fromString($fileURI)));
    }

    public function testContainsReturnsFalseIfTheFileNotExists()
    {
        $this->assertFalse($this->fileStorage->contains(StorageAgnosticFileUri::fromString('non-existing')));
    }

    public function testPutContentCreatesAFileIfItDoesNotExist()
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

    public function testPutContentUpdatesFileContentsIfTheFileDoesNotYetExist()
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

    public function testGetContentThrowsAnExceptionIfTheFileDoesNotExist()
    {
        $fileURI = 'non-existing-file';
        $this->expectException(FileDoesNotExistException::class);
        $this->expectExceptionMessage('Unable to get contents of non-existing file "non-existing-file"');
        $identifier = StorageAgnosticFileUri::fromString($fileURI);
        $this->fileStorage->getContent($identifier);
    }

    public function testGetContentReturnsTheContentsOfAnExistingFile()
    {
        $fileURI = 'non-existing-file';
        $filesystemPath = $this->testBaseDirectory . '/' . $fileURI;
        $this->createFixtureFile($filesystemPath, $this->testFileContent);

        $identifier = StorageAgnosticFileUri::fromString($fileURI);
        $fileContent = $this->fileStorage->getContent($identifier);
        
        $this->assertInstanceOf(FileContent::class, $fileContent);
        $this->assertSame($this->testFileContent, (string) $fileContent);
    }

    public function testItImplementsTheFileToFileStorageInterfaces()
    {
        $this->assertInstanceOf(FileToFileStorage::class, $this->fileStorage);
    }

    /**
     * @param string $methodWithFileArgument
     * @dataProvider methodWithFileArgumentProvider
     */
    public function testItThrowsAnExceptionIfTheFileStorageTypeDoesNotMatch($methodWithFileArgument)
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
    public function methodWithFileArgumentProvider()
    {
        return [
            'isPresent' => ['isPresent'],
            'read'      => ['read'],
            'write'     => ['write'],
        ];
    }

    public function testIsPresentReturnsFalseForANotExistingFile()
    {
        $filesystemPath = $this->testBaseDirectory . '/non-existing';
        $this->mockFile->method('getInStorageUri')->willReturn(FilesystemFileUri::fromString('/non-existing'));
        $this->mockFile->method('__toString')->willReturn($filesystemPath);

        $this->assertFalse($this->fileStorage->isPresent($this->mockFile));
    }

    public function testItReturnsTrueForAnExistingFile()
    {
        $this->mockFile->method('getInStorageUri')->willReturn(FilesystemFileUri::fromString('/existing-file'));
        $filesystemPath = $this->testBaseDirectory . '/existing-file';
        $this->mockFile->method('__toString')->willReturn($filesystemPath);
        $this->createFixtureFile($filesystemPath, $this->testFileContent);

        $this->assertTrue($this->fileStorage->isPresent($this->mockFile));
    }

    public function testWriteCreatesAFileIfItDoesNotExist()
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/file-to-create');
        $filesystemPath = $this->testBaseDirectory . '/' . $fileURI;
        $this->addFileToCleanupAfterTest($filesystemPath);

        $this->mockFile->method('getInStorageUri')->willReturn(FilesystemFileUri::fromString($filesystemPath));
        $this->mockFile->method('__toString')->willReturn($filesystemPath);
        $this->mockFile->method('getContent')->willReturn($this->testFileContent);

        $this->fileStorage->write($this->mockFile);

        $this->assertFileExists($filesystemPath);
        $this->assertSame($this->testFileContent, file_get_contents($filesystemPath));
    }

    public function testWriteUpdateAnExistingFile()
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/file-to-update');
        $filesystemPath = $this->testBaseDirectory . '/' . $fileURI;
        $this->createFixtureFile($filesystemPath, 'some other content');

        $this->mockFile->method('getInStorageUri')->willReturn(FilesystemFileUri::fromString($filesystemPath));
        $this->mockFile->method('__toString')->willReturn($filesystemPath);
        $this->mockFile->method('getContent')->willReturn($this->testFileContent);

        $this->fileStorage->write($this->mockFile);

        $this->assertSame($this->testFileContent, file_get_contents($filesystemPath));
    }

    public function testReadReturnsAnExistingFilesContent()
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/file-to-read');
        $filesystemPath = $this->testBaseDirectory . '/' . $fileURI;
        $this->mockFile->method('getInStorageUri')->willReturn(FilesystemFileUri::fromString($filesystemPath));
        $this->mockFile->method('__toString')->willReturn($filesystemPath);
        $this->createFixtureFile($filesystemPath, $this->testFileContent);

        $content = $this->fileStorage->read($this->mockFile);
        
        $this->assertInternalType('string', $content);
        $this->assertSame($this->testFileContent, $content);
    }

    public function testReadThrowsAnExceptionForNonExistantFiles()
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
