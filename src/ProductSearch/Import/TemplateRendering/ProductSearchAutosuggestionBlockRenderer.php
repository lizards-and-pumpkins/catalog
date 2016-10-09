<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\Import\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;

class ProductSearchAutosuggestionBlockRenderer extends BlockRenderer
{
    public function getLayoutHandle() : string
    {
        return 'product_search_autosuggestion';
    }
}
