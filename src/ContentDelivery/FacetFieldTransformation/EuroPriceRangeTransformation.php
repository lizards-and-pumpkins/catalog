<?php

namespace LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;

class EuroPriceRangeTransformation implements FacetFieldTransformation
{
    const PRICE_BASE = 100;
    const DECIMAL_POINTS = 2;

    /**
     * {@inheritdoc}
     */
    public function __invoke($input, Context $context)
    {
        if (!preg_match('/^\d+' . SearchEngine::RANGE_DELIMITER . '\d+$/', $input)) {
            return $input;
        }

        $range = explode(SearchEngine::RANGE_DELIMITER, $input);

        return sprintf(
            '%s € - %s €',
            number_format($range[0]/self::PRICE_BASE, self::DECIMAL_POINTS, ',', '.'),
            number_format($range[1]/self::PRICE_BASE, self::DECIMAL_POINTS, ',', '.')
        );
    }
}
