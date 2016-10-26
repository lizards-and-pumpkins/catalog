<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\FileStorage\File;
use LizardsAndPumpkins\Import\ImageStorage\Image;
use LizardsAndPumpkins\Import\Product\Image\ProductImage;

interface ProductImageFileLocator
{
    public function get(string $imageFileName, string $imageVariantCode, Context $context) : File;

    /**
     * @param string $imageVariantCode
     * @param Context $context
     * @return Image
     */
    public function getPlaceholder(string $imageVariantCode, Context $context);

    /**
     * @return string[]
     */
    public function getVariantCodes() : array;
}
