<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;

interface IntegrationTestSearchEngineOperation
{
    public function matches(SearchDocument $searchDocument) : bool;
}
