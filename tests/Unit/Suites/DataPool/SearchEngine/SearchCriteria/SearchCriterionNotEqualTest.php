<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionNotEqual
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 */
class SearchCriterionNotEqualTest extends AbstractSearchCriterionTest
{
    final protected function getOperationName() : string
    {
        return 'NotEqual';
    }

    /**
     * @return array[]
     */
    final public function getNonMatchingValues() : array
    {
        return [
            [['foo'], 'foo'],
        ];
    }

    /**
     * @return array[]
     */
    final public function getMatchingValues() : array
    {
        return[
            [['foo'], 'bar'],
        ];
    }
}
