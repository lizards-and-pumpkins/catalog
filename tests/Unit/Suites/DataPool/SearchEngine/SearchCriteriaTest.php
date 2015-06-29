<?php

namespace Brera\DataPool\SearchEngine;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchCriteria
 */
class SearchCriteriaTest extends \PHPUnit_Framework_TestCase
{
    public function testJsonSerializableInterfaceIsImplemented()
    {
        $result = SearchCriteria::create(SearchCriteria::AND_CONDITION);

        $this->assertInstanceOf(\JsonSerializable::class, $result);
    }

    public function testExeptionIsThrownIfCriteriaConditionIsInvalid()
    {
        $this->setExpectedException(InvalidCriteriaConditionException::class);
        SearchCriteria::create('foo');
    }

    public function testCriteriaConditionIsReturned()
    {
        $condition = SearchCriteria::AND_CONDITION;

        $criteria = SearchCriteria::create($condition);

        $this->assertEquals($condition, $criteria->getCondition());
    }

    public function testCriteriaArrayIsReturned()
    {
        $mockCriterion1 = $this->getMock(SearchCriterion::class, [], [], '', false);
        $mockCriterion2 = $this->getMock(SearchCriterion::class, [], [], '', false);

        $criteria = SearchCriteria::create(SearchCriteria::AND_CONDITION);
        $criteria->add($mockCriterion1);
        $criteria->add($mockCriterion2);

        $result = $criteria->getCriteria();

        $this->assertEquals([$mockCriterion1, $mockCriterion2], $result);
    }

    public function testArrayRepresentationOfCriteriaIsReturned()
    {
        $condition = SearchCriteria::AND_CONDITION;

        $criteria = SearchCriteria::create($condition);
        $result = $criteria->jsonSerialize();
        $expectation = ['condition' => $condition, 'criteria' => []];

        $this->assertSame($expectation, $result);
    }
}
