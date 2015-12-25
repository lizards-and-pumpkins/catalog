<?php

namespace LizardsAndPumpkins\Product\ProductSearch;

use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\Product;

interface SearchableAttributeValueCollector
{
    /**
     * @param Product $product
     * @param AttributeCode $attributeCode
     * @return string[]
     */
    public function getValues(Product $product, AttributeCode $attributeCode);
}
