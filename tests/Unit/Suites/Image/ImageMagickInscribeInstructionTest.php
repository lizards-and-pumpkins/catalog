<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\ImageMagickInscribeInstruction
 * @uses   \Brera\Image\ResizeInstructionTrait
 */
class ImageMagickInscribeInstructionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldImplementImageProcessorInstructionInterface()
    {
        $instruction = new ImageMagickInscribeInstruction(1, 1, 'none');
        $this->assertInstanceOf(ImageProcessorInstruction::class, $instruction);
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidImageDimensionException
     * @expectedExceptionMessage Expected integer as image width, got string.
     */
    public function itShouldFailIfWidthIsNotAnInteger()
    {
        (new ImageMagickInscribeInstruction('foo', 1, 'none'))->execute('');
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidImageDimensionException
     * @expectedExceptionMessage Image width should be greater then zero, got 0.
     */
    public function itShouldFailIfWidthIsNotPositive()
    {
        (new ImageMagickInscribeInstruction(0, 1, 'none'))->execute('');
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidImageDimensionException
     * @expectedExceptionMessage Expected integer as image height, got string.
     */
    public function itShouldFailIfHeightIsNotAnInteger()
    {
        (new ImageMagickInscribeInstruction(1, 'foo', 'none'))->execute('');
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidImageDimensionException
     * @expectedExceptionMessage Image height should be greater then zero, got -1.
     */
    public function itShouldFailIfHeightIsNotPositive()
    {
        (new ImageMagickInscribeInstruction(1, -1, 'none'))->execute('');
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidColorException
     */
    public function itShouldFailIfInvalidBackgroundColorIsSpecified()
    {
        (new ImageMagickInscribeInstruction(1, 1, 'foo'))->execute('');
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidBinaryImageDataException
     */
    public function itShouldFailIfImageStreamIsNotValid()
    {
        (new ImageMagickInscribeInstruction(1, 1, 'none'))->execute('');
    }

    /**
     * @test
     * @expectedException \Brera\Image\InvalidBinaryImageDataException
     */
    public function itShouldFailIfImageFormatIsNotSupported()
    {
        $imageStream = file_get_contents(__DIR__ . '/../../../shared-fixture/blank.ico');

        (new ImageMagickInscribeInstruction(1, 1, 'none'))->execute($imageStream);
    }

    /**
     * @test
     */
    public function itShouldResizeImageToGivenDimensions()
    {
        $requiredWidth = 15;
        $requiredHeight = 10;

        $imageStream = file_get_contents(__DIR__ . '/../../../shared-fixture/test_image2.jpg');

        $result = (new ImageMagickInscribeInstruction($requiredWidth, $requiredHeight, 'none'))->execute($imageStream);
        $resultImageInfo = getimagesizefromstring($result);

        $this->assertEquals($requiredWidth, $resultImageInfo[0]);
        $this->assertEquals($requiredHeight, $resultImageInfo[1]);
        $this->assertEquals('image/jpeg', $resultImageInfo['mime']);
    }
}
