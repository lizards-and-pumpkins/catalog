<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

class SearchCriterionLike extends SearchCriterion
{
    /**
     * @param string $searchDocumentFieldValue
     * @param string $criterionValue
     * @return bool
     */
    protected function hasValueMatchingOperator($searchDocumentFieldValue, $criterionValue)
    {
        return stripos($searchDocumentFieldValue, $criterionValue) !== false;
    }
}
