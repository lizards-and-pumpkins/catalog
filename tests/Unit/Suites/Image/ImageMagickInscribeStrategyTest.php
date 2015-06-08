<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\ImageMagickInscribeStrategy
 * @uses   \Brera\Image\ResizeStrategyTrait
 */
class ImageMagickInscribeStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldImplementImageProcessorStrategyInterface()
    {
        $strategy = new ImageMagickInscribeStrategy(1, 1, 'none');
        $this->assertInstanceOf(ImageProcessingStrategy::class, $strategy);
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidImageDimensionException
     * @expectedExceptionMessage Expected integer as image width, got string.
     */
    public function itShouldFailIfWidthIsNotAnInteger()
    {
        (new ImageMagickInscribeStrategy('foo', 1, 'none'))->processBinaryImageData('');
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidImageDimensionException
     * @expectedExceptionMessage Image width should be greater then zero, got 0.
     */
    public function itShouldFailIfWidthIsNotPositive()
    {
        (new ImageMagickInscribeStrategy(0, 1, 'none'))->processBinaryImageData('');
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidImageDimensionException
     * @expectedExceptionMessage Expected integer as image height, got string.
     */
    public function itShouldFailIfHeightIsNotAnInteger()
    {
        (new ImageMagickInscribeStrategy(1, 'foo', 'none'))->processBinaryImageData('');
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidImageDimensionException
     * @expectedExceptionMessage Image height should be greater then zero, got -1.
     */
    public function itShouldFailIfHeightIsNotPositive()
    {
        (new ImageMagickInscribeStrategy(1, -1, 'none'))->processBinaryImageData('');
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidColorException
     */
    public function itShouldFailIfInvalidBackgroundColorIsSpecified()
    {
        (new ImageMagickInscribeStrategy(1, 1, 'foo'))->processBinaryImageData('');
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidBinaryImageDataException
     */
    public function itShouldFailIfImageStreamIsNotValid()
    {
        (new ImageMagickInscribeStrategy(1, 1, 'none'))->processBinaryImageData('');
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidBinaryImageDataException
     */
    public function itShouldFailIfImageFormatIsNotSupported()
    {
        $imageStream = file_get_contents(__DIR__ . '/../../../shared-fixture/blank.ico');

        (new ImageMagickInscribeStrategy(1, 1, 'none'))->processBinaryImageData($imageStream);
    }

    /**
     * @test
     */
    public function itShouldResizeImageToGivenDimensions()
    {
        $requiredWidth = 15;
        $requiredHeight = 10;

        $imageStream = file_get_contents(__DIR__ . '/../../../shared-fixture/test_image2.jpg');

        $result = (new ImageMagickInscribeStrategy($requiredWidth, $requiredHeight, 'none'))->processBinaryImageData($imageStream);
        $resultImageInfo = getimagesizefromstring($result);

        $this->assertEquals($requiredWidth, $resultImageInfo[0]);
        $this->assertEquals($requiredHeight, $resultImageInfo[1]);
        $this->assertEquals('image/jpeg', $resultImageInfo['mime']);
    }
}
