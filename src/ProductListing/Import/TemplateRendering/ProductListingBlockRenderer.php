<?php

namespace LizardsAndPumpkins\ProductListing\Import\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;

class ProductListingBlockRenderer extends BlockRenderer
{
    /**
     * @return string
     */
    final public function getLayoutHandle()
    {
        return 'product_listing';
    }
}
