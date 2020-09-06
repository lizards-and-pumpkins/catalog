<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\FileStorage;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\FileStorage\FileInStorage
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FileContent
 */
class FileInStorageTest extends TestCase
{
    /**
     * @var FileToFileStorage|MockObject
     */
    private $mockFileStorage;

    /**
     * @var StorageSpecificFileUri|MockObject
     */
    private $inStorageFileUri;

    /**
     * @var FileInStorage
     */
    private $fileInStorage;

    final protected function setUp(): void
    {
        $this->mockFileStorage = $this->createMock(FileToFileStorage::class);
        $this->inStorageFileUri = $this->createMock(StorageSpecificFileUri::class);
        $this->inStorageFileUri->method('__toString')->willReturn('test');
        $this->fileInStorage = FileInStorage::create($this->inStorageFileUri, $this->mockFileStorage);
    }
    
    public function testItImplementsTheFileInterface(): void
    {
        $this->assertInstanceOf(File::class, $this->fileInStorage);
    }

    /**
     * @dataProvider fileExistsInStorageProvider
     */
    public function testItDelegatesToTheStorageToCheckIfTheFileExists(bool $fileExistsInStorage): void
    {
        $this->mockFileStorage->expects($this->once())->method('isPresent')
            ->with($this->equalTo($this->fileInStorage))
            ->willReturn($fileExistsInStorage);
        $this->assertSame($fileExistsInStorage, $this->fileInStorage->exists());
    }

    /**
     * @return array[]
     */
    public function fileExistsInStorageProvider() : array
    {
        return [
            [true],
            [false],
        ];
    }

    public function testItReturnsTheFileUriAsAString(): void
    {
        $this->assertSame((string) $this->inStorageFileUri, (string) $this->fileInStorage);
    }

    public function testItReturnsTheStorageSpecificFileUri(): void
    {
        $this->assertSame($this->inStorageFileUri, $this->fileInStorage->getInStorageUri());
    }

    public function testItReturnsAFileInstanceWithInjectedContent(): void
    {
        $fileContent = FileContent::fromString('test content');
        $file = FileInStorage::createWithContent($this->inStorageFileUri, $this->mockFileStorage, $fileContent);
        
        $this->assertInstanceOf(FileInStorage::class, $file);
        $this->assertSame($fileContent, $file->getContent());
    }

    public function testItReturnTheInjectedContentEvenIfTheStorageHasTheFile(): void
    {
        $fileContent = FileContent::fromString('test content');
        $this->mockFileStorage->method('read')->willReturn('other content');
        $file = FileInStorage::createWithContent($this->inStorageFileUri, $this->mockFileStorage, $fileContent);
        
        $this->assertSame($fileContent, $file->getContent());
    }

    public function testItReturnsTheFileContentFromTheStorageIfNoneWasInjected(): void
    {
        $testContent = 'storage file content';
        $this->mockFileStorage->method('read')->willReturn($testContent);
        $file = FileInStorage::create($this->inStorageFileUri, $this->mockFileStorage);

        $this->assertSame($testContent, (string) $file->getContent());
    }
}
