<?php

namespace LizardsAndPumpkins\ProductSearch\Import;

use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\ProductDTO;

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
     * @param ProductDTO $product
     * @return DefaultAttributeValueCollector
     */
    public function forProduct(ProductDTO $product)
    {
        return $product instanceof ConfigurableProduct ?
            $this->factory->createConfigurableProductAttributeValueCollector() :
            $this->factory->createDefaultAttributeValueCollector();
    }
}
