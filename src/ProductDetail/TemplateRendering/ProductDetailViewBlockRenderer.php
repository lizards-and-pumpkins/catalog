<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;

class ProductDetailViewBlockRenderer extends BlockRenderer
{
    final public function getLayoutHandle() : string
    {
        return 'product_detail_view';
    }
}
