<?php

namespace Brera\Product;

use Brera\Renderer\BlockRenderer;

class FilterNavigationBlockRenderer extends BlockRenderer
{
    /**
     * @return string
     */
    public function getLayoutHandle()
    {
        return 'filter_navigation';
    }
}
