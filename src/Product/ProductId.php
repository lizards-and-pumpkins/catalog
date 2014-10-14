<?php

namespace Brera\Poc;

class ProductId
{
    /**
     * @var string
     */
    private $id;

    /**
     * @param string $id
     */
    protected function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->id;
    }

    /**
     * @param Sku $sku
     * @return ProductId
     */
    public static function fromSku(Sku $sku)
    {
        return new ProductId((string) $sku);
    }
} 