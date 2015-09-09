<?php

namespace Brera\DataPool\SearchEngine\SearchCriteria;

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
