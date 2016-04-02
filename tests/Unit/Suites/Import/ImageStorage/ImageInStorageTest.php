<?php

namespace LizardsAndPumpkins\Import\ImageStorage;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\FileStorage\FileContent;
use LizardsAndPumpkins\Import\FileStorage\StorageSpecificFileUri;


/**
 * @covers \LizardsAndPumpkins\Import\ImageStorage\ImageInStorage
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FileContent
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 */
class ImageInStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StorageSpecificFileUri|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubStorageSpecificFileUri;

    /**
     * @var ImageToImageStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubImageStorage;

    /**
     * @var FileContent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubFileContent;

    /**
     * @return ImageInStorage
     */
    private function createImageInStorage()
    {
        return ImageInStorage::create(
            $this->stubStorageSpecificFileUri,
            $this->stubImageStorage
        );
    }

    protected function setUp()
    {
        $this->stubStorageSpecificFileUri = $this->getMock(StorageSpecificFileUri::class);
        $this->stubImageStorage = $this->getMock(ImageToImageStorage::class);
        $this->stubFileContent = $this->getMock(FileContent::class, [], [], '', false);
    }

    public function testItImplementsTheImageInterface()
    {
        $image = $this->createImageInStorage();
        $this->assertInstanceOf(Image::class, $image);
        $this->assertInstanceOf(ImageInStorage::class, $image);
    }

    public function testItReturnsAnImageWithContent()
    {
        $image = ImageInStorage::createWithContent(
            $this->stubStorageSpecificFileUri,
            $this->stubImageStorage,
            $this->stubFileContent
        );
        $this->assertInstanceOf(ImageInStorage::class, $image);
        $this->assertSame($this->stubFileContent, $image->getContent());
    }

    public function testItReturnsTheImageUrl()
    {
        $testUrl = 'http://example.com/media/image.svg';
        $this->stubImageStorage->method('url')->willReturn($testUrl);
        $stubContext = $this->getMock(Context::class);
        
        $result = $this->createImageInStorage()->getUrl($stubContext);
        
        $this->assertInstanceOf(HttpUrl::class, $result);
        $this->assertSame($testUrl, (string) $result);
    }

    public function testItReturnsTrueIfTheStorageReportsItIsPresent()
    {
        $this->stubImageStorage->method('isPresent')->willReturn(true);
        $this->assertTrue($this->createImageInStorage()->exists());
    }

    public function testItDelegatesToTheStorageToReadTheImageContentIfItWasNotInjected()
    {
        $testFileContent = 'test content';
        $image = $this->createImageInStorage();
        $this->stubImageStorage->expects($this->once())->method('read')->with($image)->willReturn($testFileContent);

        $content = $image->getContent();

        $this->assertInstanceOf(FileContent::class, $content);
        $this->assertSame($testFileContent, (string) $content);
    }

    public function testItReturnsTheStorageSpecificFileUri()
    {
        $image = $this->createImageInStorage();
        $this->assertSame($this->stubStorageSpecificFileUri, $image->getInStorageUri());
    }

    public function testItReturnsTheImageFilePathAsAString()
    {
        $testPath = '/some/path/test.svg';
        $this->stubStorageSpecificFileUri->method('__toString')->willReturn($testPath);
        $this->assertSame($testPath, (string) $this->createImageInStorage());
    }
}
