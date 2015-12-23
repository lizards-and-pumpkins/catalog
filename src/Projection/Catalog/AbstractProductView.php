<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Product\ProductImage\ProductImage;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;
use LizardsAndPumpkins\Utils\ImageStorage\Image;

abstract class AbstractProductView implements ProductView
{
    /**
     * @return ProductImageFileLocator
     */
    abstract protected function getProductImageFileLocator();

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getOriginalProduct()->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstValueOfAttribute($attributeCode)
    {
        return $this->getOriginalProduct()->getFirstValueOfAttribute($attributeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllValuesOfAttribute($attributeCode)
    {
        return $this->getOriginalProduct()->getAllValuesOfAttribute($attributeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute($attributeCode)
    {
        return $this->getOriginalProduct()->hasAttribute($attributeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->getOriginalProduct()->getAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->getOriginalProduct()->getContext();
    }

    /**
     * @param ProductImage $productImage
     * @param string $variation
     * @return Image
     */
    private function convertImage(ProductImage $productImage, $variation)
    {
        return $this->getProductImageFileLocator()->get(
            $productImage->getFileName(),
            $variation,
            $this->getContext()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getImages($variantCode)
    {
        return array_map(function (ProductImage $productImage) use ($variantCode) {
            return $this->convertImage($productImage, $variantCode);
        }, iterator_to_array($this->getOriginalProduct()->getImages()));
    }

    /**
     * {@inheritdoc}
     */
    public function getImageCount()
    {
        return $this->getOriginalProduct()->getImageCount();
    }

    /**
     * {@inheritdoc}
     */
    public function getImageByNumber($imageNumber, $variantCode)
    {
        return $imageNumber > $this->getImageCount() ?
            $this->getPlaceholderImage($variantCode) :
            $this->convertImage($this->getOriginalProduct()->getImageByNumber($imageNumber), $variantCode);
    }

    /**
     * @param int $imageNumber
     * @param string $variantCode
     * @return HttpUrl
     */
    public function getImageUrlByNumber($imageNumber, $variantCode)
    {
        return $this->getImageByNumber($imageNumber, $variantCode)->getUrl($this->getContext());
    }

    /**
     * {@inheritdoc}
     */
    public function getImageLabelByNumber($imageNumber)
    {
        return $imageNumber > $this->getImageCount() ?
            $this->getPlaceholderImageLabel() :
            $this->getOriginalProduct()->getImageLabelByNumber($imageNumber);
    }

    /**
     * @return HttpUrl
     */
    public function getMainImageUrl($variantCode)
    {
        return $this->getImageCount() === 0 ?
            $this->getPlaceholderImageUrl($variantCode) :
            $this->getImageUrlByNumber(0, $variantCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getMainImageLabel()
    {
        return $this->getImageCount() === 0 ?
            $this->getPlaceholderImageLabel() :
            $this->getOriginalProduct()->getMainImageLabel();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->getOriginalProduct()->jsonSerialize();
    }

    /**
     * @param string $variantCode
     * @return Image
     */
    protected function getPlaceholderImage($variantCode)
    {
        return $this->getProductImageFileLocator()->getPlaceholder($variantCode, $this->getContext());
    }

    /**
     * @param string $variantCode
     * @return string
     */
    protected function getPlaceholderImageUrl($variantCode)
    {
        return $this->getPlaceholderImage($variantCode)->getUrl($this->getContext());
    }

    /**
     * @return string
     */
    protected function getPlaceholderImageLabel()
    {
        return '';
    }

    /**
     * @return array[]
     */
    final protected function getAllProductImageUrls()
    {
        $imageUrls = [];
        foreach ($this->getProductImageFileLocator()->getVariantCodes() as $variantCode) {
            $imageUrls[$variantCode] = $this->getProductImagesAsImageArray($variantCode);

            if (count($imageUrls[$variantCode]) === 0) {
                $imageUrls[$variantCode][] = $this->getPlaceholderImageArray($variantCode);
            }
        };
        return $imageUrls;
    }

    /**
     * @param string $variantCode
     * @return array[]
     */
    private function getProductImagesAsImageArray($variantCode)
    {
        return array_map(function (ProductImage $productImage) use ($variantCode) {
            return $this->imageToArray($this->convertImage($productImage, $variantCode), $productImage->getLabel());
        }, iterator_to_array($this->getOriginalProduct()->getImages()));
    }

    /**
     * @param string $variantCode
     * @return string[]
     */
    private function getPlaceholderImageArray($variantCode)
    {
        $placeholder = $this->getProductImageFileLocator()->getPlaceholder($variantCode, $this->getContext());
        return $this->imageToArray($placeholder, '');
    }

    /**
     * @param Image $image
     * @param string $label
     * @return string[]
     */
    private function imageToArray(Image $image, $label)
    {
        return ['url' => (string) $image->getUrl($this->getContext()), 'label' => $label];
    }
}
