<?php

namespace Brera\DataPool\SearchEngine\SearchCriteria;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterionNotEqual
 * @covers \Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 */
class SearchCriterionNotEqualTest extends AbstractSearchCriterionTest
{
    /**
     * @return string
     */
    final protected function getOperationName()
    {
        return 'NotEqual';
    }

    /**
     * @return array[]
     */
    final public function getNonMatchingValues()
    {
        return [
            ['foo', 'foo'],
        ];
    }

    /**
     * @return array[]
     */
    final public function getMatchingValues()
    {
        return[
            ['foo', 'bar'],
        ];
    }
}
