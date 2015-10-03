<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\SimpleProduct;
use LizardsAndPumpkins\Product\ProductId;

class ProductBuilder
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
     * @return ProductId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Context $context
     * @return SimpleProduct
     */
    public function getProductForContext(Context $context)
    {
        $attributes = $this->attributeListBuilder->getAttributeListForContext($context);
        $images = $this->imageListBuilder->getImageListForContext($context);
        return new SimpleProduct($this->getId(), $attributes, $images, $context);
    }
}
