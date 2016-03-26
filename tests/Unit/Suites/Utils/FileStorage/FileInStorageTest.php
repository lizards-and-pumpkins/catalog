<?php

namespace LizardsAndPumpkins\Utils\FileStorage;

use LizardsAndPumpkins\Import\FileStorage\File;
use LizardsAndPumpkins\Import\FileStorage\FileContent;
use LizardsAndPumpkins\Import\FileStorage\FileInStorage;
use LizardsAndPumpkins\Import\FileStorage\FileToFileStorage;
use LizardsAndPumpkins\Import\FileStorage\StorageSpecificFileUri;

/**
 * @covers \LizardsAndPumpkins\Import\FileStorage\FileInStorage
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FileContent
 */
class FileInStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FileToFileStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockFileStorage;

    /**
     * @var StorageSpecificFileUri|\PHPUnit_Framework_MockObject_MockObject
     */
    private $inStorageFileUri;

    /**
     * @var FileInStorage
     */
    private $fileInStorage;

    protected function setUp()
    {
        $this->mockFileStorage = $this->getMock(FileToFileStorage::class);
        $this->inStorageFileUri = $this->getMock(StorageSpecificFileUri::class);
        $this->inStorageFileUri->method('__toString')->willReturn('test');
        $this->fileInStorage = FileInStorage::create($this->inStorageFileUri, $this->mockFileStorage);
    }
    
    public function testItImplementsTheFileInterface()
    {
        $this->assertInstanceOf(File::class, $this->fileInStorage);
    }

    /**
     * @param bool $fileExistsInStorage
     * @dataProvider fileExistsInStorageProvider
     */
    public function testItDelegatesToTheStorageToCheckIfTheFileExists($fileExistsInStorage)
    {
        $this->mockFileStorage->expects($this->once())->method('isPresent')
            ->with($this->equalTo($this->fileInStorage))
            ->willReturn($fileExistsInStorage);
        $this->assertSame($fileExistsInStorage, $this->fileInStorage->exists());
    }

    /**
     * @return array[]
     */
    public function fileExistsInStorageProvider()
    {
        return [
            [true],
            [false],
        ];
    }

    public function testItReturnsTheFileUriAsAString()
    {
        $this->assertSame((string) $this->inStorageFileUri, (string) $this->fileInStorage);
    }

    public function testItReturnsTheStorageSpecificFileUri()
    {
        $this->assertSame($this->inStorageFileUri, $this->fileInStorage->getInStorageUri());
    }

    public function testItReturnsAFileInstanceWithInjectedContent()
    {
        $fileContent = FileContent::fromString('test content');
        $file = FileInStorage::createWithContent($this->inStorageFileUri, $this->mockFileStorage, $fileContent);
        
        $this->assertInstanceOf(FileInStorage::class, $file);
        $this->assertSame($fileContent, $file->getContent());
    }

    public function testItReturnTheInjectedContentEvenIfTheStorageHasTheFile()
    {
        $fileContent = FileContent::fromString('test content');
        $this->mockFileStorage->method('read')->willReturn('other content');
        $file = FileInStorage::createWithContent($this->inStorageFileUri, $this->mockFileStorage, $fileContent);
        
        $this->assertSame($fileContent, $file->getContent());
    }

    public function testItReturnsTheFileContentFromTheStorageIfNoneWasInjected()
    {
        $testContent = 'storage file content';
        $this->mockFileStorage->method('read')->willReturn($testContent);
        $file = FileInStorage::create($this->inStorageFileUri, $this->mockFileStorage);

        $this->assertSame($testContent, (string) $file->getContent());
    }
}
