<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

class SearchCriterionGreaterOrEqualThan extends SearchCriterion
{
    /**
     * @param mixed $searchDocumentFieldValue
     * @param mixed $criterionValue
     * @return bool
     */
    final protected function hasValueMatchingOperator($searchDocumentFieldValue, $criterionValue) : bool
    {
        return $searchDocumentFieldValue >= $criterionValue;
    }
}
