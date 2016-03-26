<?php


namespace LizardsAndPumpkins\Import\Product\Composite;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Import\Product\ProductBuilder;

class AssociatedProductListBuilder
{
    /**
     * @var ProductBuilder[]
     */
    private $productBuilders;

    /**
     * @var AssociatedProductList[]
     */
    private $memoizedProductLists = [];

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
        if (! isset($this->memoizedProductLists[(string) $context])) {
            $this->memoizedProductLists[(string) $context] = $this->createAssociatedProductList($context);
        }
        return $this->memoizedProductLists[(string) $context];
    }

    /**
     * @param Context $context
     * @return AssociatedProductList
     */
    private function createAssociatedProductList(Context $context)
    {
        $builders = array_filter($this->productBuilders, function (ProductBuilder $productBuilder) use ($context) {
            return $productBuilder->isAvailableForContext($context);
        });

        $productsForContext = array_map(function (ProductBuilder $productBuilder) use ($context) {
            return $productBuilder->getProductForContext($context);
        }, $builders);
        return new AssociatedProductList(...$productsForContext);
    }
}
