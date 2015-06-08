<?php

namespace Brera\Image;

/**
 * @covers \Brera\Image\GdResizeStrategy
 * @covers \Brera\Image\ResizeStrategyTrait
 */
class GdResizeStrategyTest extends AbstractResizeStrategyTest
{
    /**
     * @return string
     */
    protected function getResizeClassName()
    {
        return GdResizeStrategy::class;
    }
}
