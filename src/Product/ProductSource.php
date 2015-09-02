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
     * @return ProductAttributeList
     */
    public function getAttributeList()
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
        return new Product($this->getId(), $attributes);
    }
}
