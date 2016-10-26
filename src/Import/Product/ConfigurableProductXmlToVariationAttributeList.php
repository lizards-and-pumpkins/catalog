<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Import\XPathParser;

class ConfigurableProductXmlToVariationAttributeList
{
    public function createVariationAttributeList(XPathParser $parser) : ProductVariationAttributeList
    {
        $attributeCodes = array_map(function (array $node) {
            return AttributeCode::fromString($node['value']);
        }, $parser->getXmlNodesArrayByXPath('/product/variations/attribute'));
        return new ProductVariationAttributeList(...$attributeCodes);
    }
}
