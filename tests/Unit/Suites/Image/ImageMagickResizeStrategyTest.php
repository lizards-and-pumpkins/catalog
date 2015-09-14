<?php

namespace LizardsAndPumpkins\Image;

/**
 * @covers \LizardsAndPumpkins\Image\ImageMagickResizeStrategy
 * @covers \LizardsAndPumpkins\Image\ResizeStrategyTrait
 */
class ImageMagickResizeStrategyTest extends AbstractResizeStrategyTest
{
    protected function setUp()
    {
        if (! extension_loaded('imagick')) {
            $this->markTestSkipped('The PHP extension imagick is not installed');
        }
    }
    
    /**
     * @return string
     */
    protected function getResizeClassName()
    {
        return ImageMagickResizeStrategy::class;
    }
}
