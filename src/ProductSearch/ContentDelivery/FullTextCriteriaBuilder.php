<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;

interface FullTextCriteriaBuilder
{
    public function createFromString(string $queryString): SearchCriteria;
}
