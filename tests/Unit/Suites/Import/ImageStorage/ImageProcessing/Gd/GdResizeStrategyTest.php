<?php

namespace LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Gd;

use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\AbstractResizeStrategyTest;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Gd\GdResizeStrategy;

/**
 * @covers \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\Gd\GdResizeStrategy
 * @covers \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ResizeStrategyTrait
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
