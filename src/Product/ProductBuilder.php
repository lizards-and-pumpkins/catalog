<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;

class ProductBuilder
{
    /**
     * @var ProductId
     */
    private $id;

    /**
     * @var ProductAttributeListBuilder
     */
    private $attributes;

    public function __construct(ProductId $id, ProductAttributeListBuilder $attributes)
    {
        $this->id = $id;
        $this->attributes = $attributes;
    }

    /**
     * @return ProductId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ProductAttributeListBuilder
     */
    public function getAttributeListBuilder()
    {
        return $this->attributes;
    }

    /**
     * @param Context $context
     * @return Product
     */
    public function getProductForContext(Context $context)
    {
        $attributes = $this->attributes->getAttributeListForContext($context);
        return new Product($this->getId(), $attributes, $context);
    }
}
