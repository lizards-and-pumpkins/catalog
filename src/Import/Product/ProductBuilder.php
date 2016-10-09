<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;

interface ProductBuilder
{
    public function isAvailableForContext(Context $context) : bool;

    public function getProductForContext(Context $context) : Product;
}
