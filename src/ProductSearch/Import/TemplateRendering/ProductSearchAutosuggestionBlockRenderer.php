<?php

namespace LizardsAndPumpkins\ProductSearch\Import\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;

class ProductSearchAutosuggestionBlockRenderer extends BlockRenderer
{
    /**
     * @return string
     */
    public function getLayoutHandle()
    {
        return 'product_search_autosuggestion';
    }
}
