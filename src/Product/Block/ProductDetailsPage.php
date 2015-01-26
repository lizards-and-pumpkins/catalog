<?php

namespace Brera\Product\Block;

use Brera\Product\Product;
use Brera\ProjectionSourceData;
use Brera\Renderer\Block;
use Brera\Renderer\Layout;

class ProductDetailsPage extends Block
{
    /**
     * @var Product
     */
    private $product;

    public function __construct(Layout $layout, ProjectionSourceData $product)
    {
        parent::__construct($layout, $product);

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
