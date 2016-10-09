<?php

declare(strict_types=1);

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

    public function getProductForContext(Context $context) : Product
    {
        $simpleProduct = $this->simpleProductBuilder->getProductForContext($context);
        $associatedProductList = $this->associatedProductListBuilder->getAssociatedProductListForContext($context);
        return new ConfigurableProduct($simpleProduct, $this->variationAttributeList, $associatedProductList);
    }

    public function isAvailableForContext(Context $context) : bool
    {
        if (! $this->simpleProductBuilder->isAvailableForContext($context)) {
            return false;
        }
        return $this->hasProductAllVariationAttributesForContext($context);
    }

    private function hasProductAllVariationAttributesForContext(Context $context) : bool
    {
        $associatedProductList = $this->associatedProductListBuilder->getAssociatedProductListForContext($context);
        foreach ($associatedProductList->getProducts() as $product) {
            if (! $this->hasAllVariationAttributes($product)) {
                return false;
            }
        }
        return true;
    }

    private function hasAllVariationAttributes(Product $product) : bool
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
