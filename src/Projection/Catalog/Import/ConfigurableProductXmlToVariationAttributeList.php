<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Utils\XPathParser;

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
