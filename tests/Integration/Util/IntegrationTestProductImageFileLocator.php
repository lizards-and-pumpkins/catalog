<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;
use LizardsAndPumpkins\Utils\FileStorage\StorageAgnosticFileUri;
use LizardsAndPumpkins\Utils\ImageStorage\Image;
use LizardsAndPumpkins\Utils\ImageStorage\ImageStorage;

class IntegrationTestProductImageFileLocator implements ProductImageFileLocator
{
    /**
     * @var ImageStorage
     */
    private $imageStorage;

    public function __construct(ImageStorage $imageStorage)
    {
        $this->imageStorage = $imageStorage;
    }

    /**
     * @param string $imageFileName
     * @param string $imageVariantCode
     * @param Context $context
     * @return Image
     */
    public function get($imageFileName, $imageVariantCode, Context $context)
    {
        $identifierString = sprintf('product/%s/%s', $imageVariantCode, $imageFileName);
        return $this->imageStorage->getFileReference(StorageAgnosticFileUri::fromString($identifierString));
    }

    /**
     * @param string $imageVariantCode
     * @param Context $context
     * @return Image
     */
    public function getPlaceholder($imageVariantCode, Context $context)
    {
        $identifierString = sprintf('product/placeholder/%s.jpg', $imageVariantCode);
        return $this->imageStorage->getFileReference(StorageAgnosticFileUri::fromString($identifierString));
    }
}
