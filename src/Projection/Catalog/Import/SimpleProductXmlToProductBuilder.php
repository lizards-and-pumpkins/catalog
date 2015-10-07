<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\ProductTypeCode;
use LizardsAndPumpkins\Product\SimpleProduct;
use LizardsAndPumpkins\Utils\XPathParser;

class SimpleProductXmlToProductBuilder extends ProductXmlToProductBuilder
{
    /**
     * @return ProductTypeCode
     */
    public function getSupportedProductTypeCode()
    {
        return ProductTypeCode::fromString(SimpleProduct::TYPE_CODE);
    }
    
    /**
     * @param XPathParser $parser
     * @return SimpleProductBuilder
     */
    public function createProductBuilder(XPathParser $parser)
    {
        $productId = ProductId::fromString($this->getSkuFromXml($parser));
        $attributeListBuilder = $this->createProductAttributeListBuilder($parser);
        $imageListBuilder = $this->createProductImageListBuilder($parser, $productId);
        return new SimpleProductBuilder($productId, $attributeListBuilder, $imageListBuilder);
    }
}
