<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\FileStorage\FileContent;
use LizardsAndPumpkins\Import\FileStorage\FileInStorage;
use LizardsAndPumpkins\Import\FileStorage\FilesystemFileStorage;
use LizardsAndPumpkins\Import\FileStorage\FilesystemFileUri;
use LizardsAndPumpkins\Import\FileStorage\StorageAgnosticFileUri;
use LizardsAndPumpkins\Util\FileSystem\TestFileFixtureTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ImageStorage\FilesystemImageStorage
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\ImageInStorage
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FileInStorage
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FilesystemFileUri
 * @uses   \LizardsAndPumpkins\Import\FileStorage\StorageAgnosticFileUri
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FileContent
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 */
class FilesystemImageStorageTest extends TestCase
{
    use TestFileFixtureTrait;
    
    /**
     * @var FilesystemImageStorage
     */
    private $imageStorage;

    /**
     * @var FilesystemFileStorage
     */
    private $mockFilesystemFileStorage;

    /**
     * @var string
     */
    private $testMediaBaseDirectory;

    /**
     * @var Image
     */
    private $mockImage;

    /**
     * @var HttpUrl
     */
    private $testMediaBaseUrl;

    final protected function setUp(): void
    {
        $this->testMediaBaseDirectory = $this->getUniqueTempDir() . '/media/';
        $testFileUri = FilesystemFileUri::fromString($this->testMediaBaseDirectory . '/test/image.svg');
        $this->mockFilesystemFileStorage = $this->createMock(FilesystemFileStorage::class);
        $testFile = FileInStorage::create($testFileUri, $this->mockFilesystemFileStorage);
        $this->mockFilesystemFileStorage->method('getFileReference')->willReturn($testFile);
        
        $this->testMediaBaseUrl = 'http://example.com/test/media';
        $stubMediaBaseUrlBuilder = $this->createMock(MediaBaseUrlBuilder::class);
        $stubMediaBaseUrlBuilder->method('create')->willReturn($this->testMediaBaseUrl);
        
        $this->mockImage = $this->createMock(Image::class);
        
        $this->imageStorage = new FilesystemImageStorage(
            $this->mockFilesystemFileStorage,
            $stubMediaBaseUrlBuilder,
            $this->testMediaBaseDirectory
        );
    }
    
    public function testItImplementsTheImageStorageInterface(): void
    {
        $this->assertInstanceOf(ImageStorage::class, $this->imageStorage);
    }

    public function testItReturnsAFileInstance(): void
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/image.svg');
        
        $image = $this->imageStorage->getFileReference($fileURI);
        
        $this->assertInstanceOf(Image::class, $image);
    }

    public function testContainsReturnsTrueIfTheFileExists(): void
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/image.svg');
        $this->mockFilesystemFileStorage->method('contains')->with($fileURI)->willReturn(true);
        
        $this->assertTrue($this->imageStorage->contains($fileURI));
    }

    public function testContainsReturnsFalseIfTheFileNotExists(): void
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/image.svg');
        $this->mockFilesystemFileStorage->method('contains')->with($fileURI)->willReturn(false);
        
        $this->assertFalse($this->imageStorage->contains($fileURI));
    }

    public function testPutContentDelegatesToTheFileStorage(): void
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/image.svg');
        $fileContent = FileContent::fromString('test content');
        
        $this->mockFilesystemFileStorage->expects($this->once())
            ->method('putContent')->with($fileURI, $fileContent);
        
        $this->imageStorage->putContent($fileURI, $fileContent);
    }

    public function testGetContentDelegatesToTheFileStorage(): void
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/image.svg');
        $stubContent = $this->createMock(FileContent::class);
        $this->mockFilesystemFileStorage->expects($this->once())
            ->method('getContent')->with($fileURI)
            ->willReturn($stubContent);

        $this->assertSame($stubContent, $this->imageStorage->getContent($fileURI));
    }

    public function testItReturnsTheHttpUrlForTheImageUri(): void
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/image.svg');
        $stubContext = $this->createMock(Context::class);
        
        $url = $this->imageStorage->getUrl($fileURI, $stubContext);
        
        $this->assertInstanceOf(HttpUrl::class, $url);
        $this->assertSame($this->testMediaBaseUrl . '/test/image.svg', (string) $url);
    }

    public function testItImplementsTheImageToImageStorageInterfaces(): void
    {
        $this->assertInstanceOf(ImageToImageStorage::class, $this->imageStorage);
    }
    
    public function testItDelegatesToTheFileStorageToCheckIfAnImageIsPresent(): void
    {
        $this->mockFilesystemFileStorage->expects($this->once())
            ->method('isPresent')->willReturn(true);
        
        $this->assertTrue($this->imageStorage->isPresent($this->mockImage));
    }

    public function testItDelegatesToTheFileStorageToReadImageContent(): void
    {
        $this->mockFilesystemFileStorage->expects($this->once())
            ->method('read')->willReturn('test content');
        
        $this->assertSame('test content', $this->imageStorage->read($this->mockImage));
    }

    public function testItDelegatesToTheFileStorageToWriteImageContent(): void
    {
        $this->mockFilesystemFileStorage->expects($this->once())
            ->method('write')->with($this->mockImage);
        
        $this->imageStorage->write($this->mockImage);
    }

    public function testItReturnsTheUrlForTheSpecifiedImage(): void
    {
        $this->mockImage->method('__toString')->willReturn($this->testMediaBaseDirectory . '/test/image.svg');
        $stubContext = $this->createMock(Context::class);
        
        $url = $this->imageStorage->url($this->mockImage, $stubContext);
        
        $this->assertIsString('string', $url);
        $this->assertSame($this->testMediaBaseUrl . '/test/image.svg', $url);
    }
}
