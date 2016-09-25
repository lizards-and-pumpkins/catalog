<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\View\ProductImageFileLocator;
use LizardsAndPumpkins\Import\FileStorage\StorageAgnosticFileUri;
use LizardsAndPumpkins\Import\ImageStorage\Image;
use LizardsAndPumpkins\Import\ImageStorage\ImageStorage;

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
    public function get($imageFileName, $imageVariantCode, Context $context) : Image // TODO: Type hints
    {
        $identifierString = sprintf('product/%s/%s', $imageVariantCode, $imageFileName);
        return $this->imageStorage->getFileReference(StorageAgnosticFileUri::fromString($identifierString));
    }

    /**
     * @param string $imageVariantCode
     * @param Context $context
     * @return Image
     */
    public function getPlaceholder($imageVariantCode, Context $context) : Image // TODO: Type hints
    {
        $identifierString = sprintf('product/placeholder/%s.jpg', $imageVariantCode);
        return $this->imageStorage->getFileReference(StorageAgnosticFileUri::fromString($identifierString));
    }

    /**
     * @return string[]
     */
    public function getVariantCodes() : array
    {
        return [
            'small',
            'medium',
            'large'
        ];
    }
}
