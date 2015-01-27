<?php

namespace Brera\Product\Block;

use Brera\Product\Product;
use Brera\ProjectionSourceData;
use Brera\Renderer\Block;

class ProductDetailsPage extends Block
{
    /**
     * @var Product
     */
    private $product;

    public function __construct($template, ProjectionSourceData $product)
    {
        parent::__construct($template, $product);

        $this->product = $product;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }
}
