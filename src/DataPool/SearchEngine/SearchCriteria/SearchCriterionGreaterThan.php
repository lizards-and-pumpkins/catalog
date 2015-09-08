<?php

namespace Brera\DataPool\SearchEngine\SearchCriteria;

class SearchCriterionGreaterThan extends SearchCriterion
{
    /**
     * @param string $searchDocumentFieldValue
     * @param string $criterionValue
     * @return bool
     */
    final protected function hasValueMatchingOperator($searchDocumentFieldValue, $criterionValue)
    {
        return $searchDocumentFieldValue > $criterionValue;
    }
}
