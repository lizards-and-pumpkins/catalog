<?php

namespace Brera\Product;

use Brera\ProjectionSourceData;

class Product implements ProjectionSourceData
{
    /**
     * @var ProductId
     */
    private $id;

    /**
     * @var ProductAttributeList
     */
    private $attributes;

    /**
     * @param ProductId $id
     * @param ProductAttributeList $attributes
     */
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
	 * @param string $code
	 * @return string
	 */
    public function getAttributeValue($code)
    {
	    /* TODO: Implement environment support */
	    $environment = [];

	    $attribute = $this->attributes->getAttribute($code, $environment);

        return $attribute->getValue();
    }
}
