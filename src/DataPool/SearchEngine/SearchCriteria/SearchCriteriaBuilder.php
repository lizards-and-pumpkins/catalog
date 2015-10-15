<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

class SearchCriteriaBuilder
{
    const FILTER_RANGE_DELIMITER = '~';

    /**
     * @param string $parameterName
     * @param string $parameterValue
     * @return SearchCriteria
     */
    public function fromRequestParameter($parameterName, $parameterValue)
    {
        $rangeMatchExpression = sprintf('/^([^%1$s]+)%1$s([^%1$s]+)/', self::FILTER_RANGE_DELIMITER);

        if (preg_match($rangeMatchExpression, $parameterValue, $range)) {
            $criterionFrom = SearchCriterionGreaterOrEqualThan::create($parameterName, $range[1]);
            $criterionTo = SearchCriterionLessOrEqualThan::create($parameterName, $range[2]);

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
