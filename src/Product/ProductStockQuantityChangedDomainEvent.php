<?php

namespace Brera\Product;

use Brera\DomainEvent;

class ProductStockQuantityChangedDomainEvent implements DomainEvent
{
    /**
     * @var string
     */
    private $payload;

    /**
     * @param string $payload
     */
    public function __construct($payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
