<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;

interface ProductBuilder
{
    /**
     * @param Context $context
     * @return bool
     */
    public function isAvailableForContext(Context $context);

    /**
     * @param Context $context
     * @return ProductDTO
     */
    public function getProductForContext(Context $context);
}
