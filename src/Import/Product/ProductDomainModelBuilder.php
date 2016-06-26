<?php

namespace LizardsAndPumpkins\Import\Product;

class ProductDomainModelBuilder
{
    /**
     * @var DetermineProductAvailability
     */
    private $determineProductAvailability;

    public function __construct(DetermineProductAvailability $determineProductAvailability)
    {
        $this->determineProductAvailability = $determineProductAvailability;
    }

    public function create(ProductDTO $productDTO)
    {
        return $productDTO->hasType(ConfigurableProduct::TYPE_CODE) ?
            ConfigurableProduct::fromDTO($productDTO) :
            SimpleProduct::fromDTO($productDTO, $this->determineProductAvailability);
    }
}
