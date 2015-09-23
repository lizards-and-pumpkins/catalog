<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

class SearchCriteriaBuilder
{
    const FILTER_RANGE_DELIMITER = '~';

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
}
