<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\XPathParser;

interface ProductXmlToProductBuilder
{
    public function getSupportedProductTypeCode() : ProductTypeCode;

    public function createProductBuilder(XPathParser $parser) : ProductBuilder;
}
