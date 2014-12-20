<?php

namespace Brera\PoC\Product;

use Brera\PoC\ProjectionSourceData;

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
	 * @param $code
	 * @return mixed|null
	 */
    public function getAttribute($code)
    {
	    /* TODO: Implement environment support */
	    $environment = [];

        return $this->attributes->getAttribute($code, $environment);
    }
}
