<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;

class ProductInSearchAutosuggestionBlockRenderer extends BlockRenderer
{
    public function getLayoutHandle() : string
    {
        return 'product_in_autosuggestion';
    }
}
