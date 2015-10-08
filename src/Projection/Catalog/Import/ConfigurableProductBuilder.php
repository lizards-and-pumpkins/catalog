<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\Composite\ProductVariationAttributeList;

class ConfigurableProductBuilder implements ProductBuilder
{
    /**
     * @var SimpleProductBuilder
     */
    private $simpleProductBuilder;
    
    /**
     * @var ProductVariationAttributeList
     */
    private $variationAttributeList;
    
    /**
     * @var AssociatedProductListBuilder
     */
    private $associatedProductListBuilder;

    public function __construct(
        SimpleProductBuilder $simpleProductBuilder,
        ProductVariationAttributeList $variationAttributeList,
        AssociatedProductListBuilder $associatedProductListBuilder
    ) {
        $this->simpleProductBuilder = $simpleProductBuilder;
        $this->variationAttributeList = $variationAttributeList;
        $this->associatedProductListBuilder = $associatedProductListBuilder;
    }

    /**
     * @param Context $context
     * @return ConfigurableProduct
     */
    public function getProductForContext(Context $context)
    {
        $simpleProduct = $this->simpleProductBuilder->getProductForContext($context);
        $associatedProductList = $this->associatedProductListBuilder->getAssociatedProductListForContext($context);
        return new ConfigurableProduct($simpleProduct, $this->variationAttributeList, $associatedProductList);
    }
}
