<?php

namespace Brera\ImageProcessor;

/**
 * @covers \Brera\ImageProcessor\ImageMagicImageProcessor
 */
class ImageMagickImageProcessorTest extends ImageProcessorTest
{
    /**
     * @return string
     */
    final protected function getImageProcessorClassName()
    {
        return ImageMagickImageProcessor::class;
    }
}
