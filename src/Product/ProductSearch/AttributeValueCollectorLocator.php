<?php

namespace LizardsAndPumpkins\Product\ProductSearch;

use LizardsAndPumpkins\MasterFactory;
use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\Product;

class AttributeValueCollectorLocator
{
    /**
     * @var MasterFactory
     */
    private $factory;

    public function __construct(MasterFactory $factory)
    {
        $this->factory = $factory;
    }
    
    /**
     * @param Product $product
     * @return DefaultAttributeValueCollector
     */
    public function forProduct(Product $product)
    {
        return $product instanceof ConfigurableProduct ?
            $this->factory->createConfigurableProductAttributeValueCollector() :
            $this->factory->createDefaultAttributeValueCollector();
    }
}
