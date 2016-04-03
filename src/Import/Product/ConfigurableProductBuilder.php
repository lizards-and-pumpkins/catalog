<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\Composite\AssociatedProductListBuilder;
use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;

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

    /**
     * @param Context $context
     * @return bool
     */
    public function isAvailableForContext(Context $context)
    {
        if (! $this->simpleProductBuilder->isAvailableForContext($context)) {
            return false;
        }
        return $this->hasProductAllVariationAttributesForContext($context);
    }

    /**
     * @param Context $context
     * @return bool
     */
    private function hasProductAllVariationAttributesForContext(Context $context)
    {
        $associatedProductList = $this->associatedProductListBuilder->getAssociatedProductListForContext($context);
        foreach ($associatedProductList->getProducts() as $product) {
            if (! $this->hasAllVariationAttributes($product)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param Product $product
     * @return bool
     */
    private function hasAllVariationAttributes($product)
    {
        $requiredAttributes = $this->variationAttributeList->getAttributes();
        foreach ($requiredAttributes as $attributeCode) {
            if (!$product->hasAttribute($attributeCode)) {
                return false;
            }
        }
        return true;
    }
}
