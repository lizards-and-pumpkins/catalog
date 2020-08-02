<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Image;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\ProductId;

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

    public static function fromImageArrays(
        ProductId $productId,
        array ...$productImageArrayList
    ) : ProductImageListBuilder {
        $productImageLists = array_map(function (array $imageArray) use ($productId) {
            return ProductImageBuilder::fromArray($productId, $imageArray);
        }, $productImageArrayList);
        return new self(...$productImageLists);
    }

    public function getImageListForContext(Context $context): ProductImageList
    {
        $images = array_map(function (ProductImageBuilder $imageBuilder) use ($context) {
            return $imageBuilder->getImageForContext($context);
        }, $this->imageBuilders);
        return new ProductImageList(...$images);
    }
}
