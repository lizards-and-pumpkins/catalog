<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

class ProductId
{
    /**
     * @var string
     */
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function __toString() : string
    {
        return (string)$this->id;
    }
}
