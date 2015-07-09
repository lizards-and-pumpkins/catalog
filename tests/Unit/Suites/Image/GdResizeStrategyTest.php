<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\GdResizeStrategy
 * @covers \Brera\Image\ResizeStrategyTrait
 */
class GdResizeStrategyTest extends AbstractResizeStrategyTest
{
    protected function setUp()
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('The PHP extension gd is not installed');
        }
    }
    
    /**
     * @return string
     */
    protected function getResizeClassName()
    {
        return GdResizeStrategy::class;
    }
}
