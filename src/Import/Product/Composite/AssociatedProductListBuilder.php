<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Composite;

use LizardsAndPumpkins\Context\Context;
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

    public function getAssociatedProductListForContext(Context $context) : AssociatedProductList
    {
        if (! isset($this->memoizedProductLists[(string) $context])) {
            $this->memoizedProductLists[(string) $context] = $this->createAssociatedProductList($context);
        }
        return $this->memoizedProductLists[(string) $context];
    }

    private function createAssociatedProductList(Context $context) : AssociatedProductList
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
