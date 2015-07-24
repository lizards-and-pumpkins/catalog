<?php

namespace Brera\Product;

use Brera\Renderer\BlockRenderer;
use Brera\Renderer\InvalidDataObjectException;

class ProductInListingBlockRenderer extends BlockRenderer
{
    /**
     * @return string
     */
    public function getLayoutHandle()
    {
        return 'product_in_listing';
    }

    /**
     * @return \Brera\Product\Product
     */
    public function getProduct()
    {
        $dataObject = $this->getDataObject();

        if (!($dataObject instanceof Product)) {
            throw new InvalidDataObjectException(
                sprintf('Data object must be instance of Product, got %s.', gettype($dataObject))
            );
        }

        return $dataObject;
    }
}
