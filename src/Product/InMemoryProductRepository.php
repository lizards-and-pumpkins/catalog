<?php

namespace Brera\PoC\Product;

class InMemoryProductRepository implements ProductRepository
{
    /**
     * @var Product[]
     */
    private $products = [];

    /**
     * @param ProductId $productId
     * @param string $productName
     * @return Product
     */
    public function createProduct(ProductId $productId, $productName)
    {
        $product = new Product($productId, $productName);
        $this->products[(string) $productId] = $product;
        return $product;
    }

    /**
     * @param ProductId $productId
     * @return Product
     * @throws ProductNotFoundException
     */
    public function findById(ProductId $productId)
    {
        $key = (string) $productId;
        if (! array_key_exists($key, $this->products)) {
            throw new ProductNotFoundException(sprintf('Unable to find product "%s"', $productId));
        }
        return $this->products[$key];
    }

    /**
     * @return null
     */
    public function commit()
    {
        // This is a stub because the InMemory product repository is only used for testing
    }
} 
