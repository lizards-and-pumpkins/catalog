<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 */
class SearchCriterionGreaterThanTest extends AbstractSearchCriterionTest
{
    final protected function getOperationName() : string
    {
        return 'GreaterThan';
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
        ];
    }
}
