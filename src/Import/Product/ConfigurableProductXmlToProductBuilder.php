<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Composite\AssociatedProductListBuilder;
use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Import\XPathParser;

class ConfigurableProductXmlToProductBuilder implements ProductXmlToProductBuilder
{
    /**
     * @var callable
     */
    private $productXmlToProductBuilderLocatorProxy;

    public function __construct(callable $productXmlToProductBuilderLocatorProxy)
    {
        $this->productXmlToProductBuilderLocatorProxy = $productXmlToProductBuilderLocatorProxy;
    }
    
    public function getSupportedProductTypeCode() : ProductTypeCode
    {
        return ProductTypeCode::fromString(ConfigurableProduct::TYPE_CODE);
    }

    public function createProductBuilder(XPathParser $parser) : ProductBuilder
    {
        return new ConfigurableProductBuilder(
            $this->createSimpleProductBuilder($parser),
            $this->createVariationAttributeList($parser),
            $this->createAssociatedProductListBuilder($parser)
        );
    }

    private function createSimpleProductBuilder(XPathParser $parser) : SimpleProductBuilder
    {
        $converter = new SimpleProductXmlToProductBuilder();
        return $converter->createProductBuilder($parser);
    }

    private function createVariationAttributeList(XPathParser $parser) : ProductVariationAttributeList
    {
        $converter = new ConfigurableProductXmlToVariationAttributeList();
        return $converter->createVariationAttributeList($parser);
    }

    private function createAssociatedProductListBuilder(XPathParser $parser) : AssociatedProductListBuilder
    {
        $productXmlToProductBuilderLocator = call_user_func($this->productXmlToProductBuilderLocatorProxy);
        $converter = new ConfigurableProductXmlToAssociatedProductListBuilder($productXmlToProductBuilderLocator);
        return $converter->createAssociatedProductListBuilder($parser);
    }
}
