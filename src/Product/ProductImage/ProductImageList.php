<?php


namespace LizardsAndPumpkins\Product\ProductImage;

use LizardsAndPumpkins\Product\ProductImage\Exception\ProductImageListNotMutableException;

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

    /**
     * @param array[] $productImagesArray
     * @return ProductImageList
     */
    public static function fromArray(array $productImagesArray)
    {
        $images = array_map(function ($productImageArray) {
            return ProductImage::fromArray($productImageArray);
        }, $productImagesArray);
        return new self(...$images);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->images);
    }

    /**
     * @return ProductImage[]
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @return \Iterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->images);
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->images[$offset]);
    }

    /**
     * @param int $offset
     * @return ProductImage
     */
    public function offsetGet($offset)
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
    public function jsonSerialize()
    {
        return $this->images;
    }
}
