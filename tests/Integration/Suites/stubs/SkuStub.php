<?php

namespace Brera\PoC\Integration\stubs;

use Brera\PoC\Product\Sku;

class SkuStub implements Sku
{
    /**
     * @var string
     */
    private $sku;

    /**
     * @param string $sku
     */
    public function __construct($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->sku;
    }

}
