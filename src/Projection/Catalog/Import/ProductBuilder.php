<?php
namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\SimpleProduct;

interface ProductBuilder
{
    /**
     * @return ProductId
     */
    public function getId();

    /**
     * @param Context $context
     * @return SimpleProduct
     */
    public function getProductForContext(Context $context);
}
