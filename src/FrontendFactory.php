<?php

namespace Brera\PoC;

use Brera\PoC\Product\ProductId;

class FrontendFactory implements Factory
{
    use FactoryTrait;

    /**
     * @return ProductSeoUrlRouter
     */
    public function createProductSeoUrlRouter()
    {
        return new ProductSeoUrlRouter(
            $this->getMasterFactory()->createDataPoolReader(),
            $this->getMasterFactory()
        );
    }

    /**
     * @param ProductId $productId
     * @return ProductDetailHtmlPage
     */
    public function createProductDetailPage(ProductId $productId)
    {
        return new ProductDetailHtmlPage(
            $productId,
            $this->getMasterFactory()->createDataPoolReader()
        );
    }
}
