<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\Import;

use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Product;

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
    
    public function forProduct(Product $product) : DefaultAttributeValueCollector
    {
        return $product instanceof ConfigurableProduct ?
            $this->factory->createConfigurableProductAttributeValueCollector() :
            $this->factory->createDefaultAttributeValueCollector();
    }
}
