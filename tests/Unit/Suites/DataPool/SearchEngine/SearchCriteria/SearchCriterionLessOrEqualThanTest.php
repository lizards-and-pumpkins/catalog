<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLessOrEqualThan
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 */
class SearchCriterionLessOrEqualThanTest extends AbstractSearchCriterionTest
{
    /**
     * @return string
     */
    final protected function getOperationName()
    {
        return 'LessOrEqualThan';
    }

    /**
     * @return array[]
     */
    final public function getNonMatchingValues()
    {
        return [
            [['2'], '1'],
        ];
    }

    /**
     * @return array[]
     */
    final public function getMatchingValues()
    {
        return[
            [['1'], '2'],
            [['1'], '1'],
        ];
    }
}
