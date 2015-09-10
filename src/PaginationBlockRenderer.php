<?php

namespace Brera;

use Brera\Renderer\BlockRenderer;

class PaginationBlockRenderer extends BlockRenderer
{
    /**
     * @return string
     */
    public function getLayoutHandle()
    {
        return 'pagination';
    }
}
