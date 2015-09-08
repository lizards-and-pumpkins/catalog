<?php

namespace Unit\Suites\DataPool\SearchEngine\SearchCriteria;

use Brera\DataPool\SearchEngine\SearchCriteria\AbstractSearchCriterionTest;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterOrEqualThan
 * @covers \Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 */
class SearchCriterionGreaterOrEqualThanTest extends AbstractSearchCriterionTest
{
    /**
     * @return string
     */
    final protected function getOperationName()
    {
        return 'GreaterOrEqualThan';
    }

    /**
     * @return array[]
     */
    final public function getNonMatchingValues()
    {
        return [
            ['1', '2'],
        ];
    }

    /**
     * @return array[]
     */
    final public function getMatchingValues()
    {
        return[
            ['2', '1'],
            ['1', '1'],
        ];
    }
}
