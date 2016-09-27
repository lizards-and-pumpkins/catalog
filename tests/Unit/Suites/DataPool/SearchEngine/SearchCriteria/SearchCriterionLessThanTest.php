<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionLessThan
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 */
class SearchCriterionLessThanTest extends AbstractSearchCriterionTest
{
    final protected function getOperationName() : string
    {
        return 'LessThan';
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
        ];
    }
}
