<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Renderer\BlockRenderer;

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
