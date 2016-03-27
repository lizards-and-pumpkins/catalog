<?php

namespace LizardsAndPumpkins\Import\ImageStorage;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\ImageStorage\FilesystemImageStorage;
use LizardsAndPumpkins\Import\ImageStorage\Image;
use LizardsAndPumpkins\Import\ImageStorage\ImageStorage;
use LizardsAndPumpkins\Import\ImageStorage\ImageToImageStorage;
use LizardsAndPumpkins\Import\ImageStorage\MediaBaseUrlBuilder;
use LizardsAndPumpkins\TestFileFixtureTrait;
use LizardsAndPumpkins\Import\FileStorage\FileContent;
use LizardsAndPumpkins\Import\FileStorage\FileInStorage;
use LizardsAndPumpkins\Import\FileStorage\FilesystemFileStorage;
use LizardsAndPumpkins\Import\FileStorage\FilesystemFileUri;
use LizardsAndPumpkins\Import\FileStorage\StorageAgnosticFileUri;

/**
 * @covers \LizardsAndPumpkins\Import\ImageStorage\FilesystemImageStorage
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\ImageInStorage
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FileInStorage
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FilesystemFileUri
 * @uses   \LizardsAndPumpkins\Import\FileStorage\StorageAgnosticFileUri
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FileContent
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 */
class FilesystemImageStorageTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;
    
    /**
     * @var FilesystemImageStorage
     */
    private $imageStorage;

    /**
     * @var FilesystemFileStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockFilesystemFileStorage;

    /**
     * @var FilesystemFileUri
     */
    private $testFileUri;

    /**
     * @var FileInStorage
     */
    private $testFile;

    /**
     * @var string
     */
    private $testMediaBaseDirectory;

    /**
     * @var MediaBaseUrlBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubMediaBaseUrlBuilder;

    /**
     * @var Image|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockImage;

    /**
     * @var HttpUrl
     */
    private $testMediaBaseUrl;

    protected function setUp()
    {
        $this->testMediaBaseDirectory = $this->getUniqueTempDir() . '/media/';
        $this->testFileUri = FilesystemFileUri::fromString($this->testMediaBaseDirectory . '/test/image.svg');
        $this->mockFilesystemFileStorage = $this->getMock(FilesystemFileStorage::class, [], [], '', false);
        $this->testFile = FileInStorage::create($this->testFileUri, $this->mockFilesystemFileStorage);
        $this->mockFilesystemFileStorage->method('getFileReference')->willReturn($this->testFile);
        
        $this->testMediaBaseUrl = HttpUrl::fromString('http://example.com/test/media');
        $this->stubMediaBaseUrlBuilder = $this->getMock(MediaBaseUrlBuilder::class);
        $this->stubMediaBaseUrlBuilder->method('create')->willReturn($this->testMediaBaseUrl);
        
        $this->mockImage = $this->getMock(Image::class);
        
        $this->imageStorage = new FilesystemImageStorage(
            $this->mockFilesystemFileStorage,
            $this->stubMediaBaseUrlBuilder,
            $this->testMediaBaseDirectory
        );
    }
    
    public function testItImplementsTheImageStorageInterface()
    {
        $this->assertInstanceOf(ImageStorage::class, $this->imageStorage);
    }

    public function testItReturnsAFileInstance()
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/image.svg');
        
        $image = $this->imageStorage->getFileReference($fileURI);
        
        $this->assertInstanceOf(Image::class, $image);
    }

    public function testContainsReturnsTrueIfTheFileExists()
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/image.svg');
        $this->mockFilesystemFileStorage->method('contains')->with($fileURI)->willReturn(true);
        
        $this->assertTrue($this->imageStorage->contains($fileURI));
    }

    public function testContainsReturnsFalseIfTheFileNotExists()
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/image.svg');
        $this->mockFilesystemFileStorage->method('contains')->with($fileURI)->willReturn(false);
        
        $this->assertFalse($this->imageStorage->contains($fileURI));
    }

    public function testPutContentDelegatesToTheFileStorage()
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/image.svg');
        $fileContent = FileContent::fromString('test content');
        
        $this->mockFilesystemFileStorage->expects($this->once())
            ->method('putContent')->with($fileURI, $fileContent);
        
        $this->imageStorage->putContent($fileURI, $fileContent);
    }

    public function testGetContentDelegatesToTheFileStorage()
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/image.svg');
        $stubContent = $this->getMock(FileContent::class, [], [], '', false);
        $this->mockFilesystemFileStorage->expects($this->once())
            ->method('getContent')->with($fileURI)
            ->willReturn($stubContent);

        $this->assertSame($stubContent, $this->imageStorage->getContent($fileURI));
    }

    public function testItReturnsTheHttpUrlForTheImageUri()
    {
        $fileURI = StorageAgnosticFileUri::fromString('test/image.svg');
        $stubContext = $this->getMock(Context::class);
        
        $url = $this->imageStorage->getUrl($fileURI, $stubContext);
        
        $this->assertInstanceOf(HttpUrl::class, $url);
        $this->assertSame($this->testMediaBaseUrl . '/test/image.svg', (string) $url);
    }

    public function testItImplementsTheImageToImageStorageInterfaces()
    {
        $this->assertInstanceOf(ImageToImageStorage::class, $this->imageStorage);
    }
    
    public function testItDelegatesToTheFileStorageToCheckIfAnImageIsPresent()
    {
        $this->mockFilesystemFileStorage->expects($this->once())
            ->method('isPresent')->willReturn(true);
        
        $this->assertTrue($this->imageStorage->isPresent($this->mockImage));
    }

    public function testItDelegatesToTheFileStorageToReadImageContent()
    {
        $this->mockFilesystemFileStorage->expects($this->once())
            ->method('read')->willReturn('test content');
        
        $this->assertSame('test content', $this->imageStorage->read($this->mockImage));
    }

    public function testItDelegatesToTheFileStorageToWriteImageContent()
    {
        $this->mockFilesystemFileStorage->expects($this->once())
            ->method('write')->with($this->mockImage);
        
        $this->imageStorage->write($this->mockImage);
    }

    public function testItReturnsTheUrlForTheSpecifiedImage()
    {
        $this->mockImage->method('__toString')->willReturn($this->testMediaBaseDirectory . '/test/image.svg');
        $stubContext = $this->getMock(Context::class);
        
        $url = $this->imageStorage->url($this->mockImage, $stubContext);
        
        $this->assertInternalType('string', $url);
        $this->assertSame($this->testMediaBaseUrl . '/test/image.svg', $url);
    }
}
