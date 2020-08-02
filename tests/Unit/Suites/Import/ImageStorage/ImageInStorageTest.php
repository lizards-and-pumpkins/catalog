<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\FileStorage\FileContent;
use LizardsAndPumpkins\Import\FileStorage\StorageSpecificFileUri;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ImageStorage\ImageInStorage
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FileContent
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 */
class ImageInStorageTest extends TestCase
{
    /**
     * @var StorageSpecificFileUri|MockObject
     */
    private $stubStorageSpecificFileUri;

    /**
     * @var ImageToImageStorage|MockObject
     */
    private $stubImageStorage;

    /**
     * @var FileContent|MockObject
     */
    private $stubFileContent;

    private function createImageInStorage() : ImageInStorage
    {
        return ImageInStorage::create(
            $this->stubStorageSpecificFileUri,
            $this->stubImageStorage
        );
    }

    final protected function setUp(): void
    {
        $this->stubStorageSpecificFileUri = $this->createMock(StorageSpecificFileUri::class);
        $this->stubImageStorage = $this->createMock(ImageToImageStorage::class);
        $this->stubFileContent = $this->createMock(FileContent::class);
    }

    public function testItImplementsTheImageInterface(): void
    {
        $image = $this->createImageInStorage();
        $this->assertInstanceOf(Image::class, $image);
        $this->assertInstanceOf(ImageInStorage::class, $image);
    }

    public function testItReturnsAnImageWithContent(): void
    {
        $image = ImageInStorage::createWithContent(
            $this->stubStorageSpecificFileUri,
            $this->stubImageStorage,
            $this->stubFileContent
        );
        $this->assertInstanceOf(ImageInStorage::class, $image);
        $this->assertSame($this->stubFileContent, $image->getContent());
    }

    public function testItReturnsTheImageUrl(): void
    {
        $testUrl = 'http://example.com/media/image.svg';
        $this->stubImageStorage->method('url')->willReturn($testUrl);
        $stubContext = $this->createMock(Context::class);
        
        $result = $this->createImageInStorage()->getUrl($stubContext);
        
        $this->assertInstanceOf(HttpUrl::class, $result);
        $this->assertSame($testUrl, (string) $result);
    }

    public function testItReturnsTrueIfTheStorageReportsItIsPresent(): void
    {
        $this->stubImageStorage->method('isPresent')->willReturn(true);
        $this->assertTrue($this->createImageInStorage()->exists());
    }

    public function testItDelegatesToTheStorageToReadTheImageContentIfItWasNotInjected(): void
    {
        $testFileContent = 'test content';
        $image = $this->createImageInStorage();
        $this->stubImageStorage->expects($this->once())->method('read')->with($image)->willReturn($testFileContent);

        $content = $image->getContent();

        $this->assertInstanceOf(FileContent::class, $content);
        $this->assertSame($testFileContent, (string) $content);
    }

    public function testItReturnsTheStorageSpecificFileUri(): void
    {
        $image = $this->createImageInStorage();
        $this->assertSame($this->stubStorageSpecificFileUri, $image->getInStorageUri());
    }

    public function testItReturnsTheImageFilePathAsAString(): void
    {
        $testPath = '/some/path/test.svg';
        $this->stubStorageSpecificFileUri->method('__toString')->willReturn($testPath);
        $this->assertSame($testPath, (string) $this->createImageInStorage());
    }
}
