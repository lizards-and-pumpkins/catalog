<?php

namespace LizardsAndPumpkins\Projection\Catalog;

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
        return $this->getOriginalProduct()->getImageByNumber($imageNumber);
    }

    /**
     * {@inheritdoc}
     */
    public function getImageFileNameByNumber($imageNumber)
    {
        return $this->getOriginalProduct()->getImageFileNameByNumber($imageNumber);
    }

    /**
     * {@inheritdoc}
     */
    public function getImageLabelByNumber($imageNumber)
    {
        return $this->getOriginalProduct()->getImageLabelByNumber($imageNumber);
    }

    /**
     * {@inheritdoc}
     */
    public function getMainImageFileName()
    {
        return $this->getOriginalProduct()->getMainImageFileName();
    }

    /**
     * {@inheritdoc}
     */
    public function getMainImageLabel()
    {
        return $this->getOriginalProduct()->getMainImageLabel();
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
}
