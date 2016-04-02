<?php

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageMagick;

use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidBinaryImageDataException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidColorException;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Exception\InvalidImageDimensionException;

use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessingStrategy;

/**
 * @covers \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageMagick\ImageMagickInscribeStrategy
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ResizeStrategyTrait
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
        $this->expectException(InvalidImageDimensionException::class);
        $this->expectExceptionMessage('Expected integer as image width, got string.');
        (new ImageMagickInscribeStrategy('foo', 1, 'none'))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfWidthIsNotPositive()
    {
        $this->expectException(InvalidImageDimensionException::class);
        $this->expectExceptionMessage('Image width should be greater then zero, got 0.');
        (new ImageMagickInscribeStrategy(0, 1, 'none'))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfHeightIsNotAnInteger()
    {
        $this->expectException(InvalidImageDimensionException::class);
        $this->expectExceptionMessage('Expected integer as image height, got string.');
        (new ImageMagickInscribeStrategy(1, 'foo', 'none'))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfHeightIsNotPositive()
    {
        $this->expectException(InvalidImageDimensionException::class);
        $this->expectExceptionMessage('Image height should be greater then zero, got -1.');
        (new ImageMagickInscribeStrategy(1, -1, 'none'))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfInvalidBackgroundColorIsSpecified()
    {
        $this->expectException(InvalidColorException::class);
        (new ImageMagickInscribeStrategy(1, 1, 'foo'))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfImageStreamIsNotValid()
    {
        $this->expectException(InvalidBinaryImageDataException::class);
        (new ImageMagickInscribeStrategy(1, 1, 'none'))->processBinaryImageData('');
    }

    public function testExceptionIsThrownIfImageFormatIsNotSupported()
    {
        $this->expectException(InvalidBinaryImageDataException::class);

        $imageStream = file_get_contents(__DIR__ . '/../../../../../../shared-fixture/blank.ico');

        (new ImageMagickInscribeStrategy(1, 1, 'none'))->processBinaryImageData($imageStream);
    }

    public function testImageIsResizedToGivenDimensions()
    {
        $requiredWidth = 15;
        $requiredHeight = 10;

        $imageStream = file_get_contents(__DIR__ . '/../../../../../../shared-fixture/test_image2.jpg');

        $imageMagickInscribeStrategy = new ImageMagickInscribeStrategy($requiredWidth, $requiredHeight, 'none');
        $result = $imageMagickInscribeStrategy->processBinaryImageData($imageStream);
        $resultImageInfo = getimagesizefromstring($result);

        $this->assertEquals($requiredWidth, $resultImageInfo[0]);
        $this->assertEquals($requiredHeight, $resultImageInfo[1]);
        $this->assertEquals('image/jpeg', $resultImageInfo['mime']);
    }
}
