<?php


namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Import\XPathParser;

class ConfigurableProductXmlToVariationAttributeList
{
    /**
     * @param XPathParser $parser
     * @return ProductVariationAttributeList
     */
    public function createVariationAttributeList(XPathParser $parser)
    {
        $attributeCodes = array_map(function (array $node) {
            return AttributeCode::fromString($node['value']);
        }, $parser->getXmlNodesArrayByXPath('/product/variations/attribute'));
        return new ProductVariationAttributeList(...$attributeCodes);
    }
}
