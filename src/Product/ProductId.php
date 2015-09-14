<?php

namespace Brera\Product;

use Brera\Product\Exception\InvalidProductIdException;

class ProductId
{
    /**
     * @var string
     */
    private $id;

    /**
     * @param string $id
     */
    private function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->id;
    }

    /**
     * @param string $productId
     * @return ProductId
     */
    public static function fromString($productId)
    {
        if (!is_string($productId)) {
            throw new InvalidProductIdException(sprintf('Can not create product ID from %s.', gettype($productId)));
        }

        return new ProductId((string) $productId);
    }
}
