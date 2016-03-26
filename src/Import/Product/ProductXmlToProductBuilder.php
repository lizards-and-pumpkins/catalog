<?php


namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\ProductTypeCode;
use LizardsAndPumpkins\Import\XPathParser;

interface ProductXmlToProductBuilder
{
    /**
     * @return ProductTypeCode
     */
    public function getSupportedProductTypeCode();

    /**
     * @param XPathParser $parser
     * @return ProductBuilder
     */
    public function createProductBuilder(XPathParser $parser);
}
