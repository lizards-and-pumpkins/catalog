<?php

namespace Brera\ImageProcessor;

/**
 * @covers \Brera\ImageProcessor\ImageProcessor
 */
abstract class ImageProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImageProcessor
     */
    private $processor;

    /**
     * @var string
     */
    private $imageFileNameForSaving;

    /**
     * @param $imagePath
     * @return string
     */
    abstract protected function getImageProcessor($imagePath);

    /**
     * @return ImageProcessor
     */
    final protected function getProcessor()
    {
        return $this->processor;
    }

    protected function setUp()
    {
        $this->processor = $this->getImageProcessor($this->getTestImage());
        $this->imageFileNameForSaving = tempnam(sys_get_temp_dir(), 'image_processor_test_');
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
     * @dataProvider invalidPathDataProvider
     * @expectedException \Brera\ImageProcessor\InvalidImageException
     * @param string $invalidPath
     */
    public function itShouldThrowAnExceptionOnWronImagePath($invalidPath)
    {
        $this->getProcessor()->setImage($invalidPath);
    }

    /**
     * @test
     */
    public function itShouldWorkAfterSetFilename()
    {
        $this->getProcessor()->setImage($this->getTestImage2());
        $this->assertEquals($this->getTestImage2(), $this->getProcessor()->getImage());

        $this->getProcessor()->setImage($this->getTestImage());
        $this->assertEquals($this->getProcessor()->getImage(), $this->getTestImage());

        $this->getProcessor()->saveAsFile($this->imageFileNameForSaving);
        $this->assertTrue(is_file($this->imageFileNameForSaving));
        $this->assertEquals(getimagesize($this->imageFileNameForSaving), getimagesize($this->getTestImage()));
    }

    /**
     * @test
     * @dataProvider invalidPathDataProvider
     * @expectedException \Brera\ImageProcessor\ImageSaveFailedException
     * @param string $invalidPath
     */
    public function itShouldThrowAnExceptionWhenAnInvalidPathForSavingIsPassed($invalidPath)
    {
        $this->processor->saveAsFile($invalidPath);
    }

    /**
     * @return mixed[]
     */
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
        $this->assertTrue($this->getProcessor()->saveAsFile($this->imageFileNameForSaving));
        list($width, $height) = getimagesize($this->imageFileNameForSaving);
        list($originalWidth, $originalHeight) = getimagesize($this->getTestImage());
        $this->assertEquals($width, $widthToResize);
        $newHeight = ($originalHeight * $width) / $originalWidth;
        $this->assertEquals($height, $newHeight, 'The new height differs more than 1%.', $newHeight / 100);
    }

    /**
     * @test
     */
    public function itShouldResizeAnImage()
    {
        $widthToResize = 200;
        $heightToResize = 200;
        $this->processor->resize($widthToResize, $heightToResize);
        $this->assertTrue($this->getProcessor()->saveAsFile($this->imageFileNameForSaving));
        list($width, $height) = getimagesize($this->imageFileNameForSaving);
        $this->assertEquals($width, $widthToResize);
        $this->assertEquals($height, $heightToResize);
    }

    /**
     * @test
     */
    public function itShouldResizeAnImageToBestFit()
    {
        $widthToResize = 200;
        $heightToResize = 200;
        $this->processor->resizeToBestFit($widthToResize, $heightToResize);
        $this->assertTrue($this->getProcessor()->saveAsFile($this->imageFileNameForSaving));
        list($width, $height) = getimagesize($this->imageFileNameForSaving);
        list($originalWidth, $originalHeight) = getimagesize($this->getTestImage());
        $this->assertEquals($width, $widthToResize);
        $newHeight = ($originalHeight * $width) / $originalWidth;
        $this->assertEquals($height, $newHeight, 'The new height differs more than 1%.', $newHeight / 100);
    }

    /**
     * @test
     */
    public function itShouldResizeAnImageToACertainHeight()
    {
        $heightToResize = 200;
        $this->processor->resizeToHeight($heightToResize);
        $this->assertTrue($this->getProcessor()->saveAsFile($this->imageFileNameForSaving));
        list($width, $height) = getimagesize($this->imageFileNameForSaving);
        list($originalWidth, $originalHeight) = getimagesize($this->getTestImage());
        $this->assertEquals($height, $heightToResize);
        $newWidth = ($originalWidth * $height) / $originalHeight;
        $this->assertEquals($width, $newWidth, 'The new height differs more than 1%.', $newWidth / 100);
    }

    /**
     * @test
     */
    public function itShouldSaveAProcessedImage()
    {
        $this->assertTrue($this->getProcessor()->saveAsFile($this->imageFileNameForSaving));
        $this->assertTrue(is_file($this->imageFileNameForSaving));
        $this->assertEquals(getimagesize($this->imageFileNameForSaving), getimagesize($this->getTestImage()));
    }

    /**
     * @return string
     */
    private function getTestImage()
    {
        return __DIR__ . '/../../../shared-fixture/test_image.jpg';
    }

    /**
     * @return string
     */
    private function getTestImage2()
    {
        return __DIR__ . '/../../../shared-fixture/test_image2.jpg';
    }

    protected function tearDown()
    {
        unlink($this->imageFileNameForSaving);
    }
}
