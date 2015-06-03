<?php

namespace Brera\DataPool\SearchEngine;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchCriteria
 */
class SearchCriteriaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Brera\DataPool\SearchEngine\InvalidCriteriaConditionException
     */
    public function itShouldFailIfCriteriaConditionIsInvalid()
    {
        SearchCriteria::create('foo');
    }

    /**
     * @test
     */
    public function itShouldReturnCriteriaCondition()
    {
        $condition = SearchCriteria::AND_CONDITION;

        $criteria = SearchCriteria::create($condition);

        $this->assertEquals($condition, $criteria->getCondition());
    }

    /**
     * @test
     */
    public function itShouldReturnArrayOfCriteria()
    {
        $mockCriterion1 = $this->getMock(SearchCriterion::class, [], [], '', false);
        $mockCriterion2 = $this->getMock(SearchCriterion::class, [], [], '', false);

        $criteria = SearchCriteria::create(SearchCriteria::AND_CONDITION);
        $criteria->add($mockCriterion1);
        $criteria->add($mockCriterion2);

        $result = $criteria->getCriteria();

        $this->assertEquals([$mockCriterion1, $mockCriterion2], $result);
    }
}
