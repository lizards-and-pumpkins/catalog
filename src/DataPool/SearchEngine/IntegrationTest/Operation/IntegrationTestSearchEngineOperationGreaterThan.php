<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\IntegrationTest\Operation;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;

class IntegrationTestSearchEngineOperationGreaterThan implements IntegrationTestSearchEngineOperation
{
    /**
     * @var IntegrationTestSearchEnginePrimitiveOperator
     */
    private $operator;

    /**
     * @param mixed[] $dataSet
     */
    public function __construct(array $dataSet)
    {
        $this->operator = new IntegrationTestSearchEnginePrimitiveOperator($dataSet);
    }

    public function matches(SearchDocument $searchDocument) : bool
    {
        return $this->operator->matches($searchDocument, function ($searchDocumentValue, $criteriaValue) {
            return $searchDocumentValue > $criteriaValue;
        });
    }
}
