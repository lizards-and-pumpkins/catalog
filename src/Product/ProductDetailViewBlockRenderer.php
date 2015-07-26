<?php

namespace Brera\Product;

use Brera\Renderer\BlockRenderer;
use Brera\Renderer\InvalidDataObjectException;

class ProductDetailViewBlockRenderer extends BlockRenderer
{
    /**
     * @return string
     */
    final public function getLayoutHandle()
    {
        return 'product_detail_view';
    }

    /**
     * @return \Brera\Product\Product
     */
    public function getProduct()
    {
        $dataObject = $this->getDataObject();

        if (!($dataObject instanceof Product)) {
            throw new InvalidDataObjectException(sprintf(
                'Data object must be instance of Product, got %s.',
                ('object' !== gettype($dataObject) ? gettype($dataObject) : get_class($dataObject))
            ));
        }

        return $dataObject;
    }
}
