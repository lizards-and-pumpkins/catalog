<?php

namespace Unit\Suites\DataPool\SearchEngine\SearchCriteria;

use Brera\DataPool\SearchEngine\SearchCriteria\AbstractSearchCriterionTest;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterionGreaterThan
 * @covers \Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 */
class SearchCriterionGreaterThanTest extends AbstractSearchCriterionTest
{
    /**
     * @return string
     */
    final protected function getOperationName()
    {
        return 'GreaterThan';
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
        ];
    }
}
