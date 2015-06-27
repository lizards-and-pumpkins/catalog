<?php

namespace Brera\Product;

use Brera\DomainCommand;

class ProjectProductQuantitySnippetDomainCommand implements DomainCommand
{
    /**
     * @var string
     */
    private $productSku;

    /**
     * @var int
     */
    private $quantity;

    /**
     * @param string $productSku
     * @param int $quantity
     */
    private function __construct($productSku, $quantity)
    {
        $this->productSku = $productSku;
        $this->quantity = $quantity;
    }

    /**
     * @param string $productSku
     * @param int $quantity
     * @return ProjectProductQuantitySnippetDomainCommand
     */
    public static function create($productSku, $quantity)
    {
        if (!is_string($productSku)) {
            throw new \InvalidArgumentException('Product SKU is supposed to be a string.');
        }

        if (!is_int($quantity)) {
            throw new \InvalidArgumentException('Product quantity is supposed to be an integer.');
        }

        return new self($productSku, $quantity);
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->productSku;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

}
