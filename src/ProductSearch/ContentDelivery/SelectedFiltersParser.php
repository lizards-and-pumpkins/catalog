<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

interface SelectedFiltersParser
{
    /**
     * @param string $filtersString
     * @return array[]
     */
    public function parse(string $filtersString): array;
}
