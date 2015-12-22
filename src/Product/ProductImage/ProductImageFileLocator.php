<?php


namespace LizardsAndPumpkins\Product\ProductImage;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Utils\ImageStorage\Image;

interface ProductImageFileLocator
{
    /**
     * @param string $imageFileName
     * @param string $imageVariantCode
     * @param Context $context
     * @return Image
     */
    public function get($imageFileName, $imageVariantCode, Context $context);
}
