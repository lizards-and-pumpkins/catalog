<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Product\ProductTypeCode;
use LizardsAndPumpkins\Utils\XPathParser;

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
