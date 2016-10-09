<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\FileStorage\File;
use LizardsAndPumpkins\Import\Product\Image\ProductImage;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductAttributeList;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\ImageStorage\Image;

interface ProductView extends \JsonSerializable
{
    public function getOriginalProduct() : Product;
    
    public function getId() : ProductId;

    public function getFirstValueOfAttribute(string $attributeCode) : string;

    /**
     * @param string $attributeCode
     * @return string[]
     */
    public function getAllValuesOfAttribute(string $attributeCode) : array;

    public function hasAttribute(string $attributeCode) : bool;

    public function getAttributes() : ProductAttributeList;

    public function getContext() : Context;

    /**
     * @param string $variation
     * @return Image[]
     */
    public function getImages(string $variation) : array;
    
    public function getImageCount() : int;

    /**
     * @param int $imageNumber
     * @param string $variation
     * @return Image
     */
    public function getImageByNumber(int $imageNumber, string $variation);

    public function getImageUrlByNumber(int $imageNumber, string $variation) : HttpUrl;
    
    public function getImageLabelByNumber(int $imageNumber) : string ;

    public function getMainImageUrl(string $variation) : HttpUrl;

    public function getMainImageLabel() : string;

    public function getProductPageTitle() : string;
}
