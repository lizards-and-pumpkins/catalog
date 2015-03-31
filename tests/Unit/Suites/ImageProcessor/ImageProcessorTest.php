<?php

namespace Brera\ImageProcessor;

abstract class ImageProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImageProcessor
     */
    private $processor;

    abstract protected function getImageProcessorClassName();

    /**
     * @return ImageProcessor
     */
    protected function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @param ImageProcessor $processor
     */
    protected function setProcessor($processor)
    {
        $this->processor = $processor;
    }

    protected function setUp()
    {
        $imageProcessorClassName = $this->getImageProcessorClassName();
        $this->setProcessor($imageProcessorClassName::fromFile($this->getTestImage()));
    }

    /**
     * @test
     */
    public function itShouldImplementImageProcessorInterface()
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->processor);
    }

    /**
     * @test
     * @expectedException \Brera\ImageProcessor\InvalidImageException
     */
    public function itShouldThrowAnExceptionIfImageIsNotReadable()
    {
        $imageProcessorClassName = $this->getImageProcessorClassName();
        $imageProcessorClassName::fromFile('some/path/non-existing-image.jpg');
    }

    /**
     * @test
     * @dataProvider invalidPathDataProvider
     * @expectedException \Brera\ImageProcessor\ImageSaveFailedException
     */
    public function itShouldThrowAnExceptionWhenAnInvalidPathForSavingIsPassed($invalidPath)
    {
        $this->processor->saveAsFile($invalidPath);
    }

    public function invalidPathDataProvider()
    {
        return [
            array([]),
            array(1),
            array(''),
            array(0.00),
            array(new \stdClass()),
            array(tmpfile()),
        ];
    }

    /**
     * @test
     */
    public function itShouldResizeAnImageToACertainWidth()
    {
        $widthToResize = 200;
        $this->processor->resizeToWidth($widthToResize);
        $filename = $this->saveImage();
        list($width, $height) = getimagesize($filename);
        list($originalWidth, $originalHeight) = getimagesize($this->getTestImage());
        $this->assertEquals($width, $widthToResize);
        $newHeight = ($originalHeight * $width) / $originalWidth;
        $this->assertEquals($height, $newHeight, 'The new height differs more than 1%. ', $newHeight / 100);
    }

    /**
     * @test
     */
    public function itShouldResizeAnImageToACertainHeight()
    {
        $heightToResize = 200;
        $this->processor->resizeToHeight($heightToResize);
        $filename = $this->saveImage();
        list($width, $height) = getimagesize($filename);
        list($originalWidth, $originalHeight) = getimagesize($this->getTestImage());
        $this->assertEquals($height, $heightToResize);
        $newWidth = ($originalWidth * $height) / $originalHeight;
        $this->assertEquals($width, $newWidth, 'The new height differs more than 1%. ', $newWidth / 100);
    }

    /**
     * @test
     */
    public function itShouldSaveAProcessedImage()
    {
        $filename = $this->saveImage();
        $this->assertTrue(is_file($filename));
        $this->assertEquals(getimagesize($filename), getimagesize($this->getTestImage()));
    }

    /**
     * @return string
     */
    private function saveImage()
    {
        $filename = tempnam(sys_get_temp_dir(), 'image_processor_test_');
        $this->getProcessor()->saveAsFile($filename);

        return $filename;
    }

    /**
     * @return string
     */
    private function getTestImage()
    {
        return __DIR__ . '/../../../shared-fixture/test_image.jpg';
    }

    protected function tearDown()
    {
        $files = glob(sys_get_temp_dir() . '/image_processor_test_*');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}
