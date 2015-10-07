<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;

class ConfigurableProductBuilder implements ProductBuilder
{
    /**
     * @var SimpleProductBuilder
     */
    private $simpleProductBuilder;
    
    /**
     * @var ProductVariationAttributeListBuilder
     */
    private $variationAttributeListBuilder;
    
    /**
     * @var AssociatedProductListBuilder
     */
    private $associatedProductListBuilder;

    public function __construct(
        SimpleProductBuilder $simpleProductBuilder,
        ProductVariationAttributeListBuilder $variationAttributeListBuilder,
        AssociatedProductListBuilder $associatedProductListBuilder
    ) {
        $this->simpleProductBuilder = $simpleProductBuilder;
        $this->variationAttributeListBuilder = $variationAttributeListBuilder;
        $this->associatedProductListBuilder = $associatedProductListBuilder;
    }
    
    public function getProductForContext(Context $context)
    {
        $simpleProduct = $this->simpleProductBuilder->getProductForContext($context);
        $variationAttributeList = $this->variationAttributeListBuilder->getVariationAttributeListForContext($context);
        $associatedProductList = $this->associatedProductListBuilder->getAssociatedProductListForContext($context);
        return new ConfigurableProduct($simpleProduct, $variationAttributeList, $associatedProductList);
    }
}
