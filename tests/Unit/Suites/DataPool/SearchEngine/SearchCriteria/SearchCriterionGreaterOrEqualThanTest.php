<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterOrEqualThan
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 */
class SearchCriterionGreaterOrEqualThanTest extends AbstractSearchCriterionTest
{
    final protected function getOperationName() : string
    {
        return 'GreaterOrEqualThan';
    }

    /**
     * @return array[]
     */
    final public function getNonMatchingValues() : array
    {
        return [
            [['1'], '2'],
        ];
    }

    /**
     * @return array[]
     */
    final public function getMatchingValues() : array
    {
        return[
            [['2'], '1'],
            [['1'], '1'],
        ];
    }
}
