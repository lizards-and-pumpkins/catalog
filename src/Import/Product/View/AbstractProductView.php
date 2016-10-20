<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\FileStorage\File;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\ProductAttribute;
use LizardsAndPumpkins\Import\Product\ProductAttributeList;
use LizardsAndPumpkins\Import\Product\Image\ProductImage;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Product\SimpleProduct;
use LizardsAndPumpkins\Import\ImageStorage\Image;

abstract class AbstractProductView implements ProductView
{
    /**
     * @var ProductAttributeList
     */
    private $memoizedProductAttributes;

    /**
     * @return ProductImageFileLocator
     */
    abstract protected function getProductImageFileLocator();

    public function getId() : ProductId
    {
        return $this->getOriginalProduct()->getId();
    }

    public function getFirstValueOfAttribute(string $attributeCode) : string
    {
        $attributeValues = $this->getAllValuesOfAttribute($attributeCode);

        if (count($attributeValues) === 0) {
            return '';
        }

        return $attributeValues[0];
    }

    /**
     * @param string $attributeCode
     * @return string[]
     */
    public function getAllValuesOfAttribute(string $attributeCode) : array
    {
        $attributeList = $this->getAttributes();

        if (!$attributeList->hasAttribute($attributeCode)) {
            return [];
        }

        return array_map(function (ProductAttribute $productAttribute) {
            return $productAttribute->getValue();
        }, $attributeList->getAttributesWithCode($attributeCode));
    }

    public function hasAttribute(string $attributeCode) : bool
    {
        return $this->getAttributes()->hasAttribute($attributeCode);
    }

    public function getAttributes() : ProductAttributeList
    {
        if (null === $this->memoizedProductAttributes) {
            $attributesArray = $this->getOriginalProduct()->getAttributes()->getAllAttributes();
            $filteredAttributes = array_filter($attributesArray, [$this, 'isAttributePublic']);
            $processedAttributes = array_map([$this, 'getProcessedAttribute'], $filteredAttributes);
            $this->memoizedProductAttributes = new ProductAttributeList(...$processedAttributes);
        }
        return $this->memoizedProductAttributes;
    }

    protected function isAttributePublic(ProductAttribute $attribute) : bool
    {
        return !in_array($attribute->getCode(), [PriceSnippetRenderer::PRICE, PriceSnippetRenderer::SPECIAL_PRICE]);
    }

    protected function getProcessedAttribute(ProductAttribute $attribute) : ProductAttribute
    {
        // Hook method to allow the processing of attribute values
        return $attribute;
    }

    public function getContext() : Context
    {
        return $this->getOriginalProduct()->getContext();
    }

    private function convertImage(ProductImage $productImage, string $variation) : File
    {
        return $this->getProductImageFileLocator()->get(
            $productImage->getFileName(),
            $variation,
            $this->getContext()
        );
    }

    /**
     * @param string $variantCode
     * @return Image[]
     */
    public function getImages(string $variantCode) : array
    {
        return array_map(function (ProductImage $productImage) use ($variantCode) {
            return $this->convertImage($productImage, $variantCode);
        }, iterator_to_array($this->getOriginalProduct()->getImages()));
    }

    public function getImageCount() : int
    {
        return $this->getOriginalProduct()->getImageCount();
    }

    /**
     * @param int $imageNumber
     * @param string $variantCode
     * @return File|ProductImage
     */
    public function getImageByNumber(int $imageNumber, string $variantCode)
    {
        return $imageNumber > $this->getImageCount() ?
            $this->getPlaceholderImage($variantCode) :
            $this->convertImage($this->getOriginalProduct()->getImageByNumber($imageNumber), $variantCode);
    }

    public function getImageUrlByNumber(int $imageNumber, string $variantCode) : HttpUrl
    {
        return $this->getImageByNumber($imageNumber, $variantCode)->getUrl($this->getContext());
    }

    public function getImageLabelByNumber(int $imageNumber) : string
    {
        return $imageNumber > $this->getImageCount() ?
            $this->getPlaceholderImageLabel() :
            $this->getOriginalProduct()->getImageLabelByNumber($imageNumber);
    }

    public function getMainImageUrl(string $variantCode) : HttpUrl
    {
        return $this->getImageCount() === 0 ?
            $this->getPlaceholderImageUrl($variantCode) :
            $this->getImageUrlByNumber(0, $variantCode);
    }

    public function getMainImageLabel() : string
    {
        return $this->getImageCount() === 0 ?
            $this->getPlaceholderImageLabel() :
            $this->getOriginalProduct()->getMainImageLabel();
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize() : array
    {
        $original = $this->getOriginalProduct()->jsonSerialize();
        return $this->transformProductJson($original);
    }

    /**
     * @param mixed[] $productData
     * @return mixed[]
     */
    protected function transformProductJson(array $productData) : array
    {
        return array_reduce(array_keys($productData), function (array $carry, $key) use ($productData) {
            switch ($key) {
                case SimpleProduct::CONTEXT:
                    $result = [];
                    break;

                case 'attributes':
                    $attributes = $this->getAttributes()->jsonSerialize();
                    $result = [$key => $this->transformAttributeDataToKeyValueMap($attributes)];
                    break;

                case 'images':
                    $result = ['images' => $this->getAllProductImageUrls()];
                    break;

                default:
                    $result = [$key => $productData[$key]];
                    break;
            }
            return array_merge($carry, $result);
        }, []);
    }

    /**
     * @param array[] $attributes
     * @return array[]
     */
    private function transformAttributeDataToKeyValueMap(array $attributes) : array
    {
        return array_reduce($attributes, function (array $carry, array $attribute) {
            $code = $attribute[ProductAttribute::CODE];
            return array_merge($carry, [$code => $this->getAttributeValue($attribute, $carry)]);
        }, []);
    }

    /**
     * @param mixed[] $attribute
     * @param string[] $carry
     * @return string|string[]
     */
    private function getAttributeValue(array $attribute, array $carry)
    {
        $code = $attribute[ProductAttribute::CODE];
        return array_key_exists($code, $carry) ?
            $this->getAttributeValuesAsArray($attribute, $carry[$code]) :
            $attribute[ProductAttribute::VALUE];
    }

    /**
     * @param mixed[] $attribute
     * @param string|string[] $existing
     * @return string[]
     */
    private function getAttributeValuesAsArray(array $attribute, $existing) : array
    {
        $existingValues = is_array($existing) ?
            $existing :
            [$existing];
        return array_merge($existingValues, [$attribute[ProductAttribute::VALUE]]);
    }

    /**
     * @param string $variantCode
     * @return ProductImage
     */
    protected function getPlaceholderImage(string $variantCode)
    {
        return $this->getProductImageFileLocator()->getPlaceholder($variantCode, $this->getContext());
    }

    protected function getPlaceholderImageUrl(string $variantCode) : HttpUrl
    {
        return $this->getPlaceholderImage($variantCode)->getUrl($this->getContext());
    }

    protected function getPlaceholderImageLabel() : string
    {
        return '';
    }

    /**
     * @return array[]
     */
    final protected function getAllProductImageUrls() : array
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
    private function getProductImagesAsImageArray(string $variantCode) : array
    {
        return array_map(function (ProductImage $productImage) use ($variantCode) {
            return $this->imageToArray($this->convertImage($productImage, $variantCode), $productImage->getLabel());
        }, iterator_to_array($this->getOriginalProduct()->getImages()));
    }

    /**
     * @param string $variantCode
     * @return string[]
     */
    private function getPlaceholderImageArray(string $variantCode) : array
    {
        $placeholder = $this->getProductImageFileLocator()->getPlaceholder($variantCode, $this->getContext());
        return $this->imageToArray($placeholder, '');
    }

    /**
     * @param Image $image
     * @param string $label
     * @return string[]
     */
    private function imageToArray(Image $image, string $label) : array
    {
        return ['url' => (string) $image->getUrl($this->getContext()), 'label' => $label];
    }
}
