<?php

namespace LizardsAndPumpkins\ProductDetail\Import\View;

use LizardsAndPumpkins\Import\Product\View\ProductView;

class TwentyOneRunProductPageTitle
{
    const MAX_PRODUCT_TITLE_LENGTH = 58;

    const PRODUCT_TITLE_SUFFIX = ' | 21run.com';

    /**
     * @param ProductView $productView
     * @return string
     */
    public function forProductView(ProductView $productView)
    {
        $base = $productView->getFirstValueOfAttribute('brand') . ' ' . $productView->getFirstValueOfAttribute('name');

        $title = array_reduce(['product_group', 'style'], function ($carry, $attributeCode) use ($productView) {
            $part = $productView->getFirstValueOfAttribute($attributeCode);
            if ('' === $part) {
                return $carry;
            }

            $combined = $carry . ' | ' . $part;

            if (strlen($combined) + strlen(self::PRODUCT_TITLE_SUFFIX) > self::MAX_PRODUCT_TITLE_LENGTH) {
                return $carry;
            }

            return $combined;
        }, $base);

        return $title . self::PRODUCT_TITLE_SUFFIX;
    }
}
