<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\ImageMagickResizeStrategy
 * @covers \Brera\Image\ResizeStrategyTrait
 */
class ImageMagickResizeStrategyTest extends AbstractResizeStrategyTest
{
    /**
     * @return string
     */
    protected function getResizeClassName()
    {
        return ImageMagickResizeStrategy::class;
    }
}
