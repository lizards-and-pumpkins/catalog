<?php

namespace Brera\ImageProcessor;

class ImageMagickTest extends ImageProcessorTest
{
    /**
     * @return string
     */
    final protected function getImageProcessorClassName()
    {
        return ImageMagicImageProcessor::class;
    }
}
