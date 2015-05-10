<?php

namespace Brera\ImageProcessor;

/**
 * @covers \Brera\ImageProcessor\ImageMagickImageProcessor
 */
class ImageMagickImageProcessorTest extends ImageProcessorTest
{
    /**
     * @param string $imagePath
     * @return string
     */
    final protected function getImageProcessor($imagePath)
    {
        return ImageMagickImageProcessor::fromFile($imagePath);
    }

    /**
     * @test
     * @expectedException \Brera\ImageProcessor\InvalidImageException
     */
    public function itShouldThrowAnExceptionIfImageIsNotReadable()
    {
        ImageMagickImageProcessor::fromFile('some/path/non-existing-image.jpg');
    }

    /**
     * @test
     */
    public function itShouldCreateAnEmptyProcessor()
    {
        $this->assertInstanceOf(ImageMagickImageProcessor::class, ImageMagickImageProcessor::fromNothing());
    }
}
