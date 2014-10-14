<?php

namespace Brera\PoC;

interface ProductRepository
{
    /**
     * @param ProductId $productId
     * @param string $productName
     * @return Product
     */
    public function createProduct(ProductId $productId, $productName);

    /**
     * @param ProductId $productId
     * @return Product
     * @throws ProductNotFoundException
     */
    public function findById(ProductId $productId);

    /**
     * @return null
     */
    public function commit();
}