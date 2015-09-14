<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

class SearchCriterionLessOrEqualThan extends SearchCriterion
{
    /**
     * @param string $searchDocumentFieldValue
     * @param string $criterionValue
     * @return bool
     */
    final protected function hasValueMatchingOperator($searchDocumentFieldValue, $criterionValue)
    {
        return $searchDocumentFieldValue <= $criterionValue;
    }
}
