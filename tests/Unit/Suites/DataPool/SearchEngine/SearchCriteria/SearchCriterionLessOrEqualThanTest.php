<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLessOrEqualThan
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 */
class SearchCriterionLessOrEqualThanTest extends AbstractSearchCriterionTest
{
    final protected function getOperationName() : string
    {
        return 'LessOrEqualThan';
    }

    /**
     * @return array[]
     */
    final public function getNonMatchingValues() : array
    {
        return [
            [['2'], '1'],
        ];
    }

    /**
     * @return array[]
     */
    final public function getMatchingValues() : array
    {
        return[
            [['1'], '2'],
            [['1'], '1'],
        ];
    }
}
