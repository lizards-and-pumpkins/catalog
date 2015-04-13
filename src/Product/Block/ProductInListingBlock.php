<?php

namespace Brera\Product\Block;

class ProductInListingBlock extends ProductBlock
{
    /**
     * @return string
     */
    public function getBrandLogoSrc()
    {
        $brandName = $this->getProductAttributeValue('brand');
        $brand = strtolower(preg_replace('/\W/', '_', trim($brandName)));
        $fileName = 'media/brands/brands-slider/' . $brand . '.png';

        if (!file_exists('pub/' . $fileName)) {
            return false;
        }

        return $fileName;
    }
}
