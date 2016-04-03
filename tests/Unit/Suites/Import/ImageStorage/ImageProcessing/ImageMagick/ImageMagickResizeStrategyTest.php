<?php

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageMagick;

use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\AbstractResizeStrategyTest;

/**
 * @covers \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageMagick\ImageMagickResizeStrategy
 * @covers \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ResizeStrategyTrait
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
