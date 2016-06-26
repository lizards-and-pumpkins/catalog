<?php

namespace LizardsAndPumpkins\ProductSearch\Import;

use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\ProductDTO;

interface AttributeValueCollector
{
    /**
     * @param ProductDTO $product
     * @param AttributeCode $attributeCode
     * @return string[]
     */
    public function getValues(ProductDTO $product, AttributeCode $attributeCode);
}
