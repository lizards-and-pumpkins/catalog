<?php

namespace LizardsAndPumpkins\Product\ProductSearch;

use LizardsAndPumpkins\MasterFactory;
use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\Product;

class SearchableAttributeValueCollectorLocator
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
     * @return DefaultSearchableAttributeValueCollector
     */
    public function forProduct(Product $product)
    {
        return $product instanceof ConfigurableProduct ?
            $this->factory->createConfigurableProductSearchableAttributeValueCollector() :
            $this->factory->createDefaultSearchableAttributeValueCollector();
    }
}
