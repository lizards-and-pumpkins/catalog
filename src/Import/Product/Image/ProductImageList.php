<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Image;

use LizardsAndPumpkins\Import\Product\Image\Exception\ProductImageListNotMutableException;

class ProductImageList implements \Countable, \IteratorAggregate, \ArrayAccess, \JsonSerializable
{
    /**
     * @var ProductImage[]
     */
    private $images;

    public function __construct(ProductImage ...$images)
    {
        $this->images = $images;
    }

    public static function fromArray(array ...$productImagesArray) : ProductImageList
    {
        $images = array_map(function ($productImageArray) {
            return ProductImage::fromArray($productImageArray);
        }, $productImagesArray);
        return new self(...$images);
    }

    public function count() : int
    {
        return count($this->images);
    }

    /**
     * @return ProductImage[]
     */
    public function getImages() : array
    {
        return $this->images;
    }

    public function getIterator() : \ArrayIterator
    {
        return new \ArrayIterator($this->images);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) : bool
    {
        return isset($this->images[$offset]);
    }

    /**
     * @param mixed $offset
     * @return ProductImage
     */
    public function offsetGet($offset) : ProductImage
    {
        return $this->images[$offset];
    }

    /**
     * @param int|string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        throw new ProductImageListNotMutableException('ProductImageList instances are immutable');
    }

    /**
     * @param int|string $offset
     */
    public function offsetUnset($offset)
    {
        throw new ProductImageListNotMutableException('ProductImageList instances are immutable');
    }

    /**
     * @return ProductImage[]
     */
    public function jsonSerialize() : array
    {
        return $this->images;
    }
}
