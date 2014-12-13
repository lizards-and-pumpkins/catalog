<?php

namespace Brera\PoC\Product;

class Product
{
    /**
     * @var ProductId
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @param ProductId $id
     * @param string    $name
     */
    public function __construct(ProductId $id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * @return ProductId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
} 
