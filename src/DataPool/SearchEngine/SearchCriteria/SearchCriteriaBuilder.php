<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;

class SearchCriteriaBuilder
{
    /**
     * @param string $parameterName
     * @param string $parameterValue
     * @return SearchCriteria
     */
    public function fromRequestParameter($parameterName, $parameterValue)
    {
        $range = explode(SearchEngine::RANGE_DELIMITER, $parameterValue);

        if (count($range) === 2) {
            $criterionFrom = SearchCriterionGreaterOrEqualThan::create($parameterName, $range[0]);
            $criterionTo = SearchCriterionLessOrEqualThan::create($parameterName, $range[1]);

            return CompositeSearchCriterion::createAnd($criterionFrom, $criterionTo);
        }

        return SearchCriterionEqual::create($parameterName, $parameterValue);
    }

    /**
     * @param string[] $fieldNames
     * @param string $queryString
     * @return CompositeSearchCriterion
     */
    public function anyOfFieldsContainString($fieldNames, $queryString)
    {
        return CompositeSearchCriterion::createOr(
            ...array_map(function ($fieldName) use ($queryString) {
                return SearchCriterionLike::create($fieldName, $queryString);
            }, $fieldNames)
        );
    }
}
