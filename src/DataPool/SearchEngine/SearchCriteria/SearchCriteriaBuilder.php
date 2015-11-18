<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;

class SearchCriteriaBuilder
{
    /**
     * @param string $fieldName
     * @param string $fieldValue
     * @return SearchCriteria
     */
    public function fromFieldNameAndValue($fieldName, $fieldValue)
    {
        $range = explode(SearchEngine::RANGE_DELIMITER, $fieldValue);

        if (count($range) === 2) {
            $criterionFrom = SearchCriterionGreaterOrEqualThan::create($fieldName, $range[0]);
            $criterionTo = SearchCriterionLessOrEqualThan::create($fieldName, $range[1]);

            return CompositeSearchCriterion::createAnd($criterionFrom, $criterionTo);
        }

        return SearchCriterionEqual::create($fieldName, $fieldValue);
    }

    /**
     * @param string[] $fieldNames
     * @param string $queryString
     * @return CompositeSearchCriterion
     */
    public function createCriteriaForAnyOfGivenFieldsContainsString(array $fieldNames, $queryString)
    {
        return CompositeSearchCriterion::createOr(
            ...array_map(function ($fieldName) use ($queryString) {
                return SearchCriterionLike::create($fieldName, $queryString);
            }, $fieldNames)
        );
    }
}
