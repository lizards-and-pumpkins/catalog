<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\ProductTypeCode;
use LizardsAndPumpkins\Utils\XPathParser;

class ConfigurableProductXmlToProductBuilder extends ProductXmlToProductBuilder
{
    /**
     * @return ProductTypeCode
     */
    public function getSupportedProductTypeCode()
    {
        return ProductTypeCode::fromString(ConfigurableProduct::TYPE_CODE);
    }

    /**
     * @param XPathParser $parser
     * @return ConfigurableProductBuilder
     */
    public function createProductBuilder(XPathParser $parser)
    {
        
        $simpleProductBuilder = (new SimpleProductXmlToProductBuilder())->createProductBuilder($parser);
        return new ConfigurableProductBuilder($simpleProductBuilder);
    }
}
