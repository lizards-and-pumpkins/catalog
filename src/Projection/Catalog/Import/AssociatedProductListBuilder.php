<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Composite\AssociatedProductList;

class AssociatedProductListBuilder
{
    /**
     * @var ProductBuilder[]
     */
    private $productBuilders;

    public function __construct(ProductBuilder ...$productBuilders)
    {
        $this->productBuilders = $productBuilders;
    }

    /**
     * @param Context $context
     * @return AssociatedProductList
     */
    public function getAssociatedProductListForContext(Context $context)
    {
        $productsForContext = array_map(function (ProductBuilder $productBuilder) use ($context) {
            return $productBuilder->getProductForContext($context);
        }, $this->productBuilders);
        return new AssociatedProductList(...$productsForContext);
    }
}
