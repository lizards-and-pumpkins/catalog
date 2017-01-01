<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;

class IntegrationTestSearchEngineOperationAnything implements IntegrationTestSearchEngineOperation
{
    public function matches(SearchDocument $searchDocument) : bool
    {
        return true;
    }
}
