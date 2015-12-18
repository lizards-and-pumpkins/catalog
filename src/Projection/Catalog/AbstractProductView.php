<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Context\ContextBuilder\ContextLocale;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductAttributeList;
use LizardsAndPumpkins\Product\ProductImage;

abstract class AbstractProductView implements ProductView
{
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
     * {@inheritdoc}
     */
    public function getImages()
    {
        return $this->getOriginalProduct()->getImages();
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
    public function getImageByNumber($imageNumber)
    {
        return $imageNumber > $this->getImageCount() ?
            $this->getPlaceholderImage() :
            $this->getOriginalProduct()->getImageByNumber($imageNumber);
    }

    /**
     * {@inheritdoc}
     */
    public function getImageFileNameByNumber($imageNumber)
    {
        return $imageNumber > $this->getImageCount() ?
            $this->getPlaceholderImageFileName() :
            $this->getOriginalProduct()->getImageFileNameByNumber($imageNumber);
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
     * {@inheritdoc}
     */
    public function getMainImageFileName()
    {
        return $this->getImageCount() === 0 ?
            $this->getPlaceholderImageFileName() :
            $this->getOriginalProduct()->getMainImageFileName();
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
    public function getTaxClass()
    {
        return $this->getOriginalProduct()->getTaxClass();
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->getOriginalProduct()->jsonSerialize();
    }

    /**
     * @return ProductImage
     */
    protected function getPlaceholderImage()
    {
        $contextData = [];
        $fileName = new ProductAttribute(ProductImage::FILE, $this->getPlaceholderImageFileName(), $contextData);
        $label = new ProductAttribute(ProductImage::LABEL, $this->getPlaceholderImageLabel(), $contextData);
        return new ProductImage(new ProductAttributeList($fileName, $label));
    }

    /**
     * @return string
     */
    protected function getPlaceholderImageFileName()
    {
        return sprintf('placeholder/placeholder-image-%s.jpg', $this->getContext()->getValue(ContextLocale::CODE));
    }

    /**
     * @return string
     */
    protected function getPlaceholderImageLabel()
    {
        return '';
    }
}
