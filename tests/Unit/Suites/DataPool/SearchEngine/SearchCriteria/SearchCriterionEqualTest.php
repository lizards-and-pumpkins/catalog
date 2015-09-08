<?php

namespace Unit\Suites\DataPool\SearchEngine\SearchCriteria;

use Brera\DataPool\SearchEngine\SearchCriteria\AbstractSearchCriterionTest;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual
 * @covers \Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 */
class SearchCriterionEqualTest extends AbstractSearchCriterionTest
{
    /**
     * @return string
     */
    final protected function getOperationName()
    {
        return 'Equal';
    }

    /**
     * @return array[]
     */
    final public function getNonMatchingValues()
    {
        return [
            ['foo', 'bar'],
        ];
    }

    /**
     * @return array[]
     */
    final public function getMatchingValues()
    {
        return[
            ['foo', 'foo'],
        ];
    }
}
