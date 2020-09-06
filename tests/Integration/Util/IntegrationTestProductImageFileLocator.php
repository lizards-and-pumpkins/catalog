<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\FileStorage\File;
use LizardsAndPumpkins\Import\Product\View\ProductImageFileLocator;
use LizardsAndPumpkins\Import\FileStorage\StorageAgnosticFileUri;
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

    public function get(string $imageFileName, string $imageVariantCode, Context $context) : File
    {
        $identifierString = sprintf('product/%s/%s', $imageVariantCode, $imageFileName);
        return $this->imageStorage->getFileReference(StorageAgnosticFileUri::fromString($identifierString));
    }

    public function getPlaceholder(string $imageVariantCode, Context $context): File
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
