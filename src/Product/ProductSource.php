<?php

namespace Brera\Product;

use Brera\Context\Context;

class ProductSource
{
    /**
     * @var ProductId
     */
    private $id;

    /**
     * @var ProductAttributeList
     */
    private $attributes;

    public function __construct(ProductId $id, ProductAttributeList $attributes)
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
     * @param Context $context
     * @return Product
     */
    public function getProductForContext(Context $context)
    {
        $attributes = $this->attributes->getAttributesForContext($context);
        return new Product($this->getId(), $attributes);
    }
}
