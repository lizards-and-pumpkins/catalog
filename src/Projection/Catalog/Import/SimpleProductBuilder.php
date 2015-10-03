<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\SimpleProduct;
use LizardsAndPumpkins\Product\ProductId;

class SimpleProductBuilder implements ProductBuilder
{
    /**
     * @var ProductId
     */
    private $id;

    /**
     * @var ProductAttributeListBuilder
     */
    private $attributeListBuilder;
    
    /**
     * @var ProductImageListBuilder
     */
    private $imageListBuilder;

    public function __construct(
        ProductId $id,
        ProductAttributeListBuilder $attributeListBuilder,
        ProductImageListBuilder $imageListBuilder
    ) {
        $this->id = $id;
        $this->attributeListBuilder = $attributeListBuilder;
        $this->imageListBuilder = $imageListBuilder;
    }

    /**
     * @param Context $context
     * @return Product
     */
    public function getProductForContext(Context $context)
    {
        $attributes = $this->attributeListBuilder->getAttributeListForContext($context);
        $images = $this->imageListBuilder->getImageListForContext($context);
        return new SimpleProduct($this->id, $attributes, $images, $context);
    }
}
