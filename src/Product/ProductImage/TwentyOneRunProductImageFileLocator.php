<?php

namespace LizardsAndPumpkins\Product\ProductImage;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder\ContextLocale;
use LizardsAndPumpkins\Product\ProductImage\Exception\InvalidImageFileNameException;
use LizardsAndPumpkins\Product\ProductImage\Exception\InvalidImageVariantCodeException;
use LizardsAndPumpkins\Utils\FileStorage\StorageAgnosticFileUri;
use LizardsAndPumpkins\Utils\ImageStorage\Image;
use LizardsAndPumpkins\Utils\ImageStorage\ImageStorage;

class TwentyOneRunProductImageFileLocator implements ProductImageFileLocator
{
    const ORIGINAL = 'original';
    const LARGE = 'large';
    const MEDIUM = 'medium';
    const SMALL = 'small';
    const SEARCH_AUTOSUGGESTION = 'search-autosuggestion';

    private $imageVariantCodes = [
        self::ORIGINAL,
        self::LARGE,
        self::MEDIUM,
        self::SMALL,
        self::SEARCH_AUTOSUGGESTION,
    ];

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
        $this->validateImageFileName($imageFileName);
        $this->validateImageVariantCode($imageVariantCode);

        $imageIdentifier = $this->buildIdentifier($imageFileName, $imageVariantCode);
        return $this->isImageFileAvailable($imageFileName, $imageIdentifier) ?
            $this->imageStorage->getFileReference($imageIdentifier) :
            $this->getPlaceholder($imageVariantCode, $context);
    }

    /**
     * @param string $imageFileName
     * @param string $imageVariantCode
     * @return StorageAgnosticFileUri
     */
    private function buildIdentifier($imageFileName, $imageVariantCode)
    {
        return $this->createIdentifierForString(sprintf('product/%s/%s', $imageVariantCode, $imageFileName));
    }

    /**
     * @param string $identifier
     * @return StorageAgnosticFileUri
     */
    private function createIdentifierForString($identifier)
    {
        return StorageAgnosticFileUri::fromString($identifier);
    }

    /**
     * @param string $imageVariantCode
     * @param Context $context
     * @return Image
     */
    public function getPlaceholder($imageVariantCode, Context $context)
    {
        $localeCode = $context->getValue(ContextLocale::CODE);
        $identifier = sprintf('product/placeholder/%s/placeholder-image-%s.jpg', $imageVariantCode, $localeCode);
        $placeholderIdentifier = $this->createIdentifierForString($identifier);
        return $this->imageStorage->getFileReference($placeholderIdentifier);
    }

    /**
     * @param string $imageVariantCode
     * @return string
     */
    private function getInvalidTypeStringRepresentation($imageVariantCode)
    {
        if (is_string($imageVariantCode)) {
            return $imageVariantCode;
        }
        if (is_object($imageVariantCode)) {
            return get_class($imageVariantCode);
        }
        return gettype($imageVariantCode);
    }

    /**
     * @param string $imageFileName
     */
    private function validateImageFileName($imageFileName)
    {
        if (!is_string($imageFileName)) {
            throw new InvalidImageFileNameException(sprintf(
                'The image file name must be a string, got "%s"',
                $this->getInvalidTypeStringRepresentation($imageFileName)
            ));
        }
    }

    /**
     * @param string $imageVariantCode
     */
    private function validateImageVariantCode($imageVariantCode)
    {
        if (!in_array($imageVariantCode, $this->imageVariantCodes)) {
            throw new InvalidImageVariantCodeException(sprintf(
                'The image variant code must be one of %s, got "%s"',
                implode(', ', $this->imageVariantCodes),
                $this->getInvalidTypeStringRepresentation($imageVariantCode)
            ));
        }
    }

    /**
     * @return string[]
     */
    public function getVariantCodes()
    {
        return $this->imageVariantCodes;
    }

    /**
     * @param string $imageFileName
     * @param StorageAgnosticFileUri $imageIdentifier
     * @return bool
     */
    private function isImageFileAvailable($imageFileName, StorageAgnosticFileUri $imageIdentifier)
    {
        return trim($imageFileName) !== '' && $this->imageStorage->contains($imageIdentifier);
    }
}
