<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;

class ProductListingBlockRenderer extends BlockRenderer
{
    final public function getLayoutHandle() : string
    {
        return 'product_listing';
    }
}
