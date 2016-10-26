<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\Image\ProductImage;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;

interface Product extends \JsonSerializable
{
    const URL_KEY = 'url_key';
    const ID = 'product_id';
    const TYPE_KEY = 'type_code';
    const NON_CANONICAL_URL_KEY = 'non_canonical_url_key';
    
    /**
     * @return ProductId
     */
    public function getId();

    /**
     * @param string $attributeCode
     * @return mixed
     */
    public function getFirstValueOfAttribute(string $attributeCode);

    /**
     * @param string $attributeCode
     * @return string[]
     */
    public function getAllValuesOfAttribute(string $attributeCode) : array;

    public function hasAttribute(AttributeCode $attributeCode) : bool;

    public function getAttributes() : ProductAttributeList;

    public function getContext() : Context;

    public function getImages() : ProductImageList;

    public function getImageCount() : int;

    public function getImageByNumber(int $imageNumber) : ProductImage;

    public function getImageFileNameByNumber(int $imageNumber) : string;

    public function getImageLabelByNumber(int $imageNumber) : string;

    public function getMainImageFileName() : string;

    public function getMainImageLabel() : string;

    public function getTaxClass() : ProductTaxClass;
}
