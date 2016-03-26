<?php


namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Composite\AssociatedProductListBuilder;
use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Import\Product\ProductTypeCode;
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
        return new ConfigurableProductBuilder(
            $this->createSimpleProductBuilder($parser),
            $this->createVariationAttributeList($parser),
            $this->createAssociatedProductListBuilder($parser)
        );
    }

    /**
     * @param XPathParser $parser
     * @return SimpleProductBuilder
     */
    private function createSimpleProductBuilder(XPathParser $parser)
    {
        $converter = new SimpleProductXmlToProductBuilder();
        return $converter->createProductBuilder($parser);
    }

    /**
     * @param XPathParser $parser
     * @return ProductVariationAttributeList
     */
    private function createVariationAttributeList(XPathParser $parser)
    {
        $converter = new ConfigurableProductXmlToVariationAttributeList();
        return $converter->createVariationAttributeList($parser);
    }

    /**
     * @param XPathParser $parser
     * @return AssociatedProductListBuilder
     */
    private function createAssociatedProductListBuilder(XPathParser $parser)
    {
        $productXmlToProductBuilderLocator = call_user_func($this->productXmlToProductBuilderLocatorProxy);
        $converter = new ConfigurableProductXmlToAssociatedProductListBuilder($productXmlToProductBuilderLocator);
        return $converter->createAssociatedProductListBuilder($parser);
    }
}
