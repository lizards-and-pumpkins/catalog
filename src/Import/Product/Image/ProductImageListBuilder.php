<?php


namespace LizardsAndPumpkins\Import\Product\Image;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;

class ProductImageListBuilder
{
    /**
     * @var ProductImageBuilder[]
     */
    private $imageBuilders;

    public function __construct(ProductImageBuilder ...$imageBuilders)
    {
        $this->imageBuilders = $imageBuilders;
    }

    /**
     * @param ProductId $productId
     * @param array[] $productImageArrayList
     * @return ProductImageListBuilder
     */
    public static function fromArray(ProductId $productId, array $productImageArrayList)
    {
        $productImageLists = array_map(function (array $imageArray) use ($productId) {
            return ProductImageBuilder::fromArray($productId, $imageArray);
        }, $productImageArrayList);
        return new self(...$productImageLists);
    }

    /**
     * @param Context $context
     * @return ProductImageList
     */
    public function getImageListForContext(Context $context)
    {
        $images = array_map(function (ProductImageBuilder $imageBuilder) use ($context) {
            return $imageBuilder->getImageForContext($context);
        }, $this->imageBuilders);
        return new ProductImageList(...$images);
    }
}
