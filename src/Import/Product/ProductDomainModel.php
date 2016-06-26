<?php

namespace LizardsAndPumpkins\Import\Product;

interface ProductDomainModel extends ProductDTO
{
    /**
     * @return bool
     */
    public function isAvailable();
}
