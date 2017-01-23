<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;

interface CriteriaParser
{
    public function createCriteriaFromString(string $criteriaString): SearchCriteria;
}
