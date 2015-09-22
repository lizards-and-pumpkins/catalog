<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

class SearchCriteriaBuilder
{
    const FILTER_RANGE_DELIMITER = '~';

    public function create($fieldName, $fieldValue)
    {
        $regularExpression = sprintf('/^([^%1$s]+)%1$s([^%1$s]+)/', self::FILTER_RANGE_DELIMITER);

        if (preg_match($regularExpression, $fieldValue, $range)) {
            $criterionFrom = SearchCriterionGreaterOrEqualThan::create($fieldName, $range[1]);
            $criterionTo = SearchCriterionLessOrEqualThan::create($fieldName, $range[2]);

            return CompositeSearchCriterion::createAnd($criterionFrom, $criterionTo);
        }

        return SearchCriterionEqual::create($fieldName, $fieldValue);
    }
}
