<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;

class ProductListingDescriptionBlockRenderer extends BlockRenderer
{
    public function getLayoutHandle() : string
    {
        return 'product_listing_description';
    }
}
