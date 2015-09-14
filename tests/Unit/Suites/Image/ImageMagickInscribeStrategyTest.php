<?php

namespace LizardsAndPumpkins\Image;

/**
 * @covers \LizardsAndPumpkins\Image\ImageMagickInscribeStrategy
 * @uses   \LizardsAndPumpkins\Image\ResizeStrategyTrait
 */
class ImageMagickInscribeStrategyTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (! extension_loaded('imagick')) {
            $this->markTestSkipped('The PHP extension imagick is not installed');
        }
    }
    
    public function testImageProcessorStrategyInterfaceIsImplemented()
    {
        $strategy = new ImageMagickInscribeStrategy(1, 1, 'none');
        $this->assertInstanceOf(ImageProcessingStrategy::class, $strategy);
    }

    public function testExceptionIsThrownIfWidthIsNotAnInteger()
    {
        $this->setExpectedException(
            InvalidImageDimensionException::class,
            'Expected integer as image width, got string.'
        );
        (new ImageMagickInscribeStrategy('foo', 1, 'none'))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfWidthIsNotPositive()
    {
        $this->setExpectedException(
            InvalidImageDimensionException::class,
            'Image width should be greater then zero, got 0.'
        );
        (new ImageMagickInscribeStrategy(0, 1, 'none'))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfHeightIsNotAnInteger()
    {
        $this->setExpectedException(
            InvalidImageDimensionException::class,
            'Expected integer as image height, got string.'
        );
        (new ImageMagickInscribeStrategy(1, 'foo', 'none'))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfHeightIsNotPositive()
    {
        $this->setExpectedException(
            InvalidImageDimensionException::class,
            'Image height should be greater then zero, got -1.'
        );
        (new ImageMagickInscribeStrategy(1, -1, 'none'))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfInvalidBackgroundColorIsSpecified()
    {
        $this->setExpectedException(InvalidColorException::class);
        (new ImageMagickInscribeStrategy(1, 1, 'foo'))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfImageStreamIsNotValid()
    {
        $this->setExpectedException(InvalidBinaryImageDataException::class);
        (new ImageMagickInscribeStrategy(1, 1, 'none'))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfImageFormatIsNotSupported()
    {
        $this->setExpectedException(InvalidBinaryImageDataException::class);

        $imageStream = file_get_contents(__DIR__ . '/../../../shared-fixture/blank.ico');

        (new ImageMagickInscribeStrategy(1, 1, 'none'))->processBinaryImageData($imageStream);
    }

    public function testImageIsResizedToGivenDimensions()
    {
        $requiredWidth = 15;
        $requiredHeight = 10;

        $imageStream = file_get_contents(__DIR__ . '/../../../shared-fixture/test_image2.jpg');

        $imageMagickInscribeStrategy = new ImageMagickInscribeStrategy($requiredWidth, $requiredHeight, 'none');
        $result = $imageMagickInscribeStrategy->processBinaryImageData($imageStream);
        $resultImageInfo = getimagesizefromstring($result);

        $this->assertEquals($requiredWidth, $resultImageInfo[0]);
        $this->assertEquals($requiredHeight, $resultImageInfo[1]);
        $this->assertEquals('image/jpeg', $resultImageInfo['mime']);
    }
}
